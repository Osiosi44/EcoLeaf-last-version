"""Run HTTP regression checks against an isolated, disposable local Docker database.

The existing EcoLeaf database is never reset. Requires the local Compose database
service and a built web image. Run: python tests/integration.py
"""
from __future__ import annotations

import base64
import concurrent.futures
import datetime
import http.cookiejar
import json
import os
from pathlib import Path
import re
import secrets
import subprocess
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
DATABASE = "ecoleaf_test_" + secrets.token_hex(5)
CONTAINER = "ecoleaf-test-" + secrets.token_hex(5)
PORT = int(os.environ.get("ECOLEAF_TEST_PORT", "8081"))
BASE = f"http://127.0.0.1:{PORT}/"
PASSWORD = secrets.token_urlsafe(18)
CHECKS: list[str] = []


def command(args: list[str], data: bytes | None = None) -> str:
    result = subprocess.run(args, input=data, cwd=ROOT, capture_output=True)
    if result.returncode:
        detail = result.stderr.decode(errors="replace").replace(PASSWORD, "[redacted]")
        print(detail[-6000:], flush=True)
        result.check_returncode()
    return result.stdout.decode()


def sql(source: str) -> str:
    return command(["docker", "compose", "exec", "-T", "database", "sh", "-c",
                    'exec mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" --batch --skip-column-names'], source.encode())


def php(source: str) -> str:
    return command(["docker", "exec", "-i", CONTAINER, "php"],
                   ("<?php require '/var/www/html/db/db_con.php'; " + source).encode())


def db_rows(source: str) -> list[dict]:
    encoded = base64.b64encode(source.encode()).decode()
    return json.loads(php(f"echo json_encode($con->query(base64_decode('{encoded}'))->fetch_all(MYSQLI_ASSOC));"))


def passed(message: str) -> None:
    CHECKS.append(message)
    print("PASS " + message, flush=True)


class Client:
    def __init__(self) -> None:
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies))

    def request(self, path: str, data: dict | list | None = None, *, json_accept: bool = False) -> tuple[int, str, str]:
        headers = {"Accept": "application/json" if json_accept else "text/html"}
        payload = None if data is None else urllib.parse.urlencode(data).encode()
        request = urllib.request.Request(BASE + path, data=payload, headers=headers)
        try:
            response = self.opener.open(request, timeout=20)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            body = response.read().decode()
            assert "Fatal error" not in body and "Warning:" not in body, (path, body[:500])
            return response.status, body, response.url

    def token(self, path: str = "login.php") -> str:
        status, body, _ = self.request(path)
        assert status == 200, (path, status)
        match = re.search(r'name="csrf_token" value="([a-f0-9]+)"', body)
        assert match, path
        return match.group(1)

    def login(self, username: str) -> None:
        token = self.token()
        status, _, destination = self.request("db/db_login.php", {
            "csrf_token": token, "username": username, "password": PASSWORD,
        })
        assert status == 200 and "login.php" not in destination, destination


def signup_data(token: str, username: str, email: str | None = None) -> dict:
    return {"csrf_token": token, "name": "Integration Test", "username": username,
            "email": email or username + "@example.test", "phone": "+251900000001", "password": PASSWORD}


def run() -> None:
    command(["docker", "exec", CONTAINER, "php", "tests/validation.php"])
    passed("Unicode text boundaries, invalid encoding, and exact decimal validation")
    guest = Client()
    for route in ["", "home.php", "package.php", "about.php", "login.php", "booking.php", "privacy.php"]:
        status, body, _ = guest.request(route)
        assert status == 200 and 'rel="icon"' in body and 'name="description"' in body, route
    assert guest.request("not-a-real-page")[0] == 404
    for path in [".git/config", "db/config.example.php", "db/app.php", "database/init.sql", "tests/integration.py"]:
        assert guest.request(path)[0] == 403, path
    passed("Public pages, metadata, favicon, custom 404, and internal-file protection")

    for path in ["db/db_login.php", "db/db_signup.php", "process_order.php", "db/db_deleteUser.php", "db/db_review.php", "db/db_logout.php", "admin/actions.php"]:
        assert guest.request(path, {}, json_accept=True)[0] == 403, path
    assert guest.request("db/db_logout.php")[0] == 405
    passed("CSRF protection on all mutation routes and POST-only logout")

    token = guest.token("login.php?mode=signup")
    invalid = signup_data(token, "invalid_fixture")
    invalid.update(email="not-an-email", password="short", phone="bad")
    _, body, _ = guest.request("db/db_signup.php", invalid)
    assert "valid email" in body and "field-error" in body
    assert not db_rows("SELECT id FROM users WHERE username='invalid_fixture'")
    _, _, destination = guest.request("db/db_signup.php", signup_data(token, "alice_test"))
    assert "login.php" in destination
    assert len(db_rows("SELECT id FROM users WHERE username='alice_test'")) == 1
    passed("Registration validation and successful customer creation")

    clients = [Client(), Client()]
    tokens = [client.token("login.php?mode=signup") for client in clients]
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
        list(pool.map(lambda pair: pair[0].request("db/db_signup.php", signup_data(pair[1], "duplicate_test")), zip(clients, tokens)))
    assert len(db_rows("SELECT id FROM users WHERE username='duplicate_test'")) == 1
    passed("Concurrent duplicate registration is serialized safely")

    alice = Client(); alice.login("alice_test")
    for route in ["admin/adminDash.php", "admin/users.php", "admin/services.php", "admin/account.php"]:
        assert alice.request(route)[0] == 403, route
    passed("Customer cannot access admin dashboard or legacy admin endpoints")

    admin_payload = base64.b64encode(json.dumps({"password": PASSWORD}).encode()).decode()
    php(f"$data=json_decode(base64_decode('{admin_payload}'),true);$hash=password_hash($data['password'],PASSWORD_DEFAULT);$stmt=$con->prepare(\"INSERT INTO users(full_name,username,email,phone_number,hashed_password,role) VALUES ('Test Administrator','admin_test','admin@example.test','+251900000002',?,'admin')\");$stmt->bind_param('s',$hash);$stmt->execute();")
    admin = Client(); admin.login("admin_test")
    admin_token = admin.token("admin/adminDash.php?tab=services")
    status, _, _ = admin.request("admin/actions.php", {"csrf_token": admin_token, "action": "add_service", "service_name": "Test garment care", "description": "Temporary integration fixture", "price": "2.75", "option_name": "Test cotton shirt"})
    assert status == 200
    option = db_rows("SELECT s.service_id,o.option_id,o.price FROM services s JOIN service_options o ON o.service_id=s.service_id WHERE s.service_name='Test garment care'")[0]
    passed("Administrator can create a bookable service")

    status, body, _ = alice.request("booking.php")
    csrf = re.search(r'name="csrf_token" value="([a-f0-9]+)"', body).group(1)
    booking_token = re.search(r'name="booking_token" value="([a-f0-9]+)"', body).group(1)
    booking = {"csrf_token": csrf, "booking_token": booking_token, "address": "Test pickup address", "pickup_date": "2000-01-01", f"items[{option['option_id']}]": "2", "total_price": "0.01"}
    assert alice.request("process_order.php", booking, json_accept=True)[0] == 422
    booking["pickup_date"] = datetime.datetime.now(datetime.timezone(datetime.timedelta(hours=3))).strftime("%Y-%m-%d")
    status, body, _ = alice.request("process_order.php", booking, json_accept=True)
    assert status == 201, body
    order_id = json.loads(body)["order_id"]
    row = db_rows(f"SELECT o.total,oi.quantity,oi.price FROM orders o JOIN order_items oi ON oi.order_id=o.order_id WHERE o.order_id={order_id}")[0]
    assert float(row["total"]) == 5.50 and int(row["quantity"]) == 2 and float(row["price"]) == 2.75, row
    status, body, _ = alice.request("process_order.php", booking, json_accept=True)
    assert status == 200 and json.loads(body)["order_id"] == order_id
    assert int(db_rows("SELECT COUNT(*) AS n FROM orders")[0]["n"]) == 1
    passed("Past-date rejection, decimal server pricing, line quantities, and idempotent booking")

    assert alice.request("db/db_review.php", {"csrf_token": csrf, "rating": "5", "comment": "A test review before completion"}, json_accept=True)[0] == 403
    bob = Client(); bob_token = bob.token("login.php?mode=signup")
    bob.request("db/db_signup.php", signup_data(bob_token, "bob_test")); bob.login("bob_test")
    bob_token = bob.token("my_orders.php")
    bob.request("db/cancel_order.php", {"csrf_token": bob_token, "order_id": order_id})
    assert db_rows(f"SELECT status FROM orders WHERE order_id={order_id}")[0]["status"] == "pending"
    assert "Test pickup address" not in bob.request("my_orders.php")[1]
    passed("Order ownership isolation and completed-order review requirement")

    profile_token = alice.token("profile.php")
    profile = {"csrf_token": profile_token, "name": '<img src=x onerror="alert(1)">', "email": "alice_test@example.test", "phone": "+251900000001", "address": "Test address", "password": ""}
    alice.request("db/profile/profile_update.php", profile)
    _, body, _ = admin.request("admin/adminDash.php?tab=users")
    assert '&lt;img src=x onerror=' in body and '<img src=x onerror=' not in body
    profile.update(email="changed@example.test", current_password="wrong")
    alice.request("db/profile/profile_update.php", profile)
    assert db_rows("SELECT email FROM users WHERE username='alice_test'")[0]["email"] == "alice_test@example.test"
    passed("Escaped admin output and current-password protection for email changes")

    for next_status, expected in [("complete", "pending"), ("in_progress", "in_progress"), ("complete", "complete")]:
        admin.request("admin/actions.php", {"csrf_token": admin_token, "action": "update_order", "order_id": order_id, "status": next_status})
        current = db_rows(f"SELECT status FROM orders WHERE order_id={order_id}")[0]["status"]
        assert current == expected, current
        if next_status == "complete" and current == "pending": passed("Invalid order-state jumps are rejected")
    assert db_rows(f"SELECT status FROM orders WHERE order_id={order_id}")[0]["status"] == "complete"
    passed("Order workflow advances scheduled → in care → completed")

    update = {"csrf_token": admin_token, "action": "save_service", "service_id": option["service_id"], "service_name": "Test garment care", "description": "Updated fixture", "price": "4.25", f"prices[{option['option_id']}]": "4.25"}
    admin.request("admin/actions.php", update)
    assert float(db_rows(f"SELECT price FROM service_options WHERE option_id={int(option['option_id'])}")[0]["price"]) == 4.25
    assert float(db_rows(f"SELECT price FROM order_items WHERE order_id={order_id}")[0]["price"]) == 2.75
    passed("Catalog editing preserves historical booking prices")
    alice.request("db/db_review.php", {"csrf_token": csrf, "rating": "5", "comment": "Automated integration test review"})
    assert len(db_rows("SELECT review_id FROM reviews")) == 1
    passed("Completed-order review can be published")

    create_admin = signup_data(admin_token, "second_admin_test")
    create_admin.update(action="create_admin", current_password="wrong")
    admin.request("admin/actions.php", create_admin)
    assert not db_rows("SELECT id FROM users WHERE username='second_admin_test'")
    create_admin["current_password"] = PASSWORD
    admin.request("admin/actions.php", create_admin)
    assert db_rows("SELECT role FROM users WHERE username='second_admin_test'")[0]["role"] == "admin"
    passed("Administrator creation requires password confirmation")

    # Preserve populated fixtures for the optional browser suite while the isolated server lives.
    browser_runner = ROOT / "tests/browser.cjs"
    if browser_runner.exists() and (ROOT / "node_modules/playwright").exists():
        payload = json.dumps({"base": BASE, "password": PASSWORD, "orderId": order_id}).encode()
        command(["node", str(browser_runner), "--fixtures"], payload)
        passed("Authenticated browser checks")

    bob.request("db/db_deleteUser.php", {"csrf_token": bob_token, "current_password": PASSWORD, "confirmation": "DELETE", "username": "alice_test"})
    assert not db_rows("SELECT id FROM users WHERE username='bob_test'")
    assert db_rows("SELECT id FROM users WHERE username='alice_test'")
    passed("Account deletion uses session ownership, not a submitted username")
    alice.request("db/db_logout.php", {"csrf_token": csrf})
    alice.login("alice_test")
    passed("Blank profile password leaves the existing password unchanged")
    token = guest.token()
    for _ in range(11):
        status, _, _ = guest.request("db/db_login.php", {"csrf_token": token, "username": "throttle_fixture", "password": "incorrect"}, json_accept=True)
    assert status == 429, status
    passed("Persistent login throttling")


if __name__ == "__main__":
    os.chdir(ROOT)
    created = False
    try:
        sql(f"CREATE DATABASE `{DATABASE}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON `{DATABASE}`.* TO 'ecoleaf'@'%';")
        created = True
        sql(f"USE `{DATABASE}`;\n" + (ROOT / "database/init.sql").read_text(encoding="utf-8"))
        command(["docker", "compose", "run", "-d", "--no-deps", "--name", CONTAINER,
                 "-e", "DB_NAME=" + DATABASE, "-p", f"127.0.0.1:{PORT}:80", "web"])
        for _ in range(30):
            try:
                with urllib.request.urlopen(BASE, timeout=2):
                    break
            except (OSError, urllib.error.URLError):
                time.sleep(0.5)
        run()
        report = {"checks": CHECKS, "count": len(CHECKS), "database": "isolated disposable database", "result": "passed"}
        report_path = ROOT / "artifacts/qa/integration-report.json"
        report_path.parent.mkdir(parents=True, exist_ok=True)
        report_path.write_text(json.dumps(report, indent=2), encoding="utf-8")
        print(f"\n{len(CHECKS)} integration groups passed.", flush=True)
    finally:
        subprocess.run(["docker", "rm", "-f", CONTAINER], capture_output=True)
        if created:
            sql(f"DROP DATABASE IF EXISTS `{DATABASE}`;")
        print("Isolated test container and database removed. Existing EcoLeaf data preserved.", flush=True)
