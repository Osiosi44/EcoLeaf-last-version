<?php
require "../db/db_con.php";

$usersQuery = "SELECT * FROM users WHERE role = 'user'";
$usersResult = $con->query($usersQuery);

if ($usersResult->num_rows > 0): ?>
    <h2>Users</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $usersResult->fetch_assoc()){ ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['full_name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                </tr>
            <?php }; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>