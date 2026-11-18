(() => {
  'use strict';
  const menuButton = document.querySelector('[data-menu-toggle]');
  const menu = document.getElementById('site-navigation');
  const closeMenu = () => { menu?.classList.remove('is-open'); menuButton?.setAttribute('aria-expanded', 'false'); menuButton?.setAttribute('aria-label', 'Open navigation'); };
  menuButton?.addEventListener('click', () => {
    const open = menuButton.getAttribute('aria-expanded') !== 'true';
    menu.classList.toggle('is-open', open); menuButton.setAttribute('aria-expanded', String(open)); menuButton.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
  });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && menu?.classList.contains('is-open')) { closeMenu(); menuButton.focus(); } });
  document.addEventListener('click', event => { if (menu?.classList.contains('is-open') && !event.target.closest('.header-inner')) closeMenu(); });
  document.querySelectorAll('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    const show = input.type === 'password'; input.type = show ? 'text' : 'password'; button.textContent = show ? 'Hide' : 'Show'; button.setAttribute('aria-pressed', String(show));
  }));
  document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
  document.querySelectorAll('form[data-submit-form]').forEach(form => form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"]'); if (!button) return;
    button.dataset.originalLabel = button.innerHTML; button.disabled = true; button.textContent = button.dataset.loadingLabel || 'Saving...';
  }));
  window.addEventListener('pageshow', () => document.querySelectorAll('[data-original-label]').forEach(button => { button.disabled = false; button.innerHTML = button.dataset.originalLabel; }));

  const booking = document.querySelector('[data-booking-form]');
  if (!booking) return;
  const inputs = [...booking.querySelectorAll('[data-option]')];
  const storageKey = 'ecoleaf-laundry-bag-v2';
  const money = cents => new Intl.NumberFormat('en-US', {style:'currency', currency:'USD'}).format(cents / 100);
  const readBag = () => { try { return JSON.parse(sessionStorage.getItem(storageKey) || '{}'); } catch { return {}; } };
  const bag = readBag();
  inputs.forEach(input => { const saved = bag?.[input.dataset.option]; if (Number.isInteger(saved) && saved >= 0 && saved <= 1000) input.value = saved; });
  function updateSummary() {
    const list = document.getElementById('summary-items'); list.replaceChildren();
    let total = 0; const saved = {};
    inputs.forEach(input => {
      const quantity = Number(input.value);
      if (!Number.isInteger(quantity) || quantity <= 0 || quantity > 1000) return;
      const subtotal = quantity * Number(input.dataset.cents); total += subtotal; saved[input.dataset.option] = quantity;
      const item = document.createElement('li'), label = document.createElement('span'), amount = document.createElement('strong');
      label.textContent = `${quantity} \u00d7 ${input.dataset.name}`; amount.textContent = money(subtotal); item.append(label, amount); list.append(item);
    });
    document.getElementById('summary-empty').hidden = total > 0;
    document.getElementById('booking-total').textContent = money(total);
    const submit = document.getElementById('booking-submit'); if (submit && !submit.dataset.busy) submit.disabled = total <= 0;
    try { sessionStorage.setItem(storageKey, JSON.stringify(saved)); } catch { /* Booking also works without browser storage. */ }
  }
  inputs.forEach(input => input.addEventListener('input', updateSummary));
  booking.querySelectorAll('[data-quantity-change]').forEach(button => button.addEventListener('click', () => {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    const value = Number(input.value) || 0;
    input.value = Math.min(1000, Math.max(0, value + Number(button.dataset.quantityChange))); updateSummary();
  }));
  updateSummary();
  let pending = false;
  booking.addEventListener('submit', async event => {
    if (booking.dataset.signedIn !== 'true') return;
    event.preventDefault(); if (pending || !booking.reportValidity()) return;
    const button = document.getElementById('booking-submit'), error = document.getElementById('booking-error');
    error.hidden = true;
    booking.querySelectorAll('[aria-invalid]').forEach(input => input.removeAttribute('aria-invalid'));
    if (!inputs.some(input => Number(input.value) > 0)) { error.textContent = 'Choose at least one service to book.'; error.hidden = false; error.scrollIntoView({block:'center', behavior:'instant'}); return; }
    pending = true; button.dataset.busy = 'true'; button.disabled = true; button.textContent = 'Confirming pickup...';
    try {
      const response = await fetch(booking.action, {method:'POST', body:new FormData(booking), headers:{Accept:'application/json'}, credentials:'same-origin'});
      const contentType = response.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) throw new Error('Your session may have expired. Reload this page and try again.');
      const data = await response.json();
      if (!response.ok) {
        if (data.errors) Object.keys(data.errors).forEach(name => booking.elements.namedItem(name)?.setAttribute?.('aria-invalid','true'));
        throw new Error(data.errors ? Object.values(data.errors).join(' ') : data.error || 'Your booking could not be saved. Please try again.');
      }
      if (!Number.isInteger(data.order_id) || !data.redirect) throw new Error('The booking response was incomplete. Reload My orders before trying again.');
      const target = new URL(data.redirect, location.href);
      if (target.origin !== location.origin) throw new Error('The booking response could not be verified.');
      try { sessionStorage.removeItem(storageKey); } catch { /* Storage is optional. */ }
      location.assign(target.href);
    } catch (failure) {
      error.textContent = failure.message || 'We could not reach the server. Check your connection, then try again.'; error.hidden = false;
      error.setAttribute('tabindex','-1'); error.focus(); pending = false; delete button.dataset.busy; button.textContent = 'Confirm pickup'; updateSummary();
    }
  });
})();
