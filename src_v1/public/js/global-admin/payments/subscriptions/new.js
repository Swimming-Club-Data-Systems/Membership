import { renderSubscriptionItems } from "./common.js";

{

  let subscriptionPlansInput = document.getElementById('subscription-plans-object');
  let subscriptionPlans = {
    products: {},
  }

  let tenantSelect = document.getElementById('subscription-tenant');
  tenantSelect.addEventListener('change', async ev => {
    // On change get this tenant's usable payment methods

    let pmSelect = document.getElementById('subscription-payment-method');
    pmSelect.disabled = true;
    pmSelect.innerHTML = '<option value="" selected disabled>Select a payment method</option>';

    try {
      // POST the FormData object
      let formData = new FormData();
      formData.append('tenant', tenantSelect.value)
      const response = await fetch(tenantSelect.dataset.paymentMethodsAjaxUrl, {
        method: 'POST',
        redirect: 'error',
        body: formData // body data type must match "Content-Type" header
      });

      let body = response.body;
      console.log(body);

      let json = await response.json();

      console.log(response);

      if (json.success) {
        // Yay
        pmSelect.disabled = false;
        pmSelect.innerHTML = json.html;
      } else {
        alert('Error\r\n' + json.error);
      }

    } catch (err) {
      console.warn(err);
    }
  });

  let productSelect = document.getElementById('product-select');
  productSelect.addEventListener('change', async ev => {
    // On change get this tenant's usable payment methods

    let planSelect = document.getElementById('plan-select');
    planSelect.disabled = true;
    planSelect.innerHTML = '<option value="" selected disabled>Select a payment method</option>';

    try {
      // POST the FormData object
      let formData = new FormData();
      formData.append('product', productSelect.value)
      const response = await fetch(productSelect.dataset.plansAjaxUrl, {
        method: 'POST',
        redirect: 'error',
        body: formData // body data type must match "Content-Type" header
      });

      let body = response.body;
      console.log(body);

      let json = await response.json();

      console.log(response);

      if (json.success) {
        // Yay
        planSelect.disabled = false;
        planSelect.innerHTML = json.html;
      } else {
        alert('Error\r\n' + json.error);
      }

    } catch (err) {
      console.warn(err);
    }
  });

  let addPlanForm = document.getElementById('add-plan-form');
  addPlanForm.addEventListener('submit', ev => {
    ev.preventDefault();
    let formData = new FormData(addPlanForm);

    let product = formData.get('product-select');
    let productName = (document.getElementById('product-select-' + product)).dataset.name;

    let plan = formData.get('plan-select');
    let planData = document.getElementById('plan-select-' + plan).dataset;

    let planObject = {
      id: plan,
      name: planData.name,
      price_per_unit: parseInt(planData.amount),
      currency: planData.currency,
      quantity: parseInt(formData.get('plan-quantity')),
      tax_rate: null,
      discount: null,
    };

    // Discount is object

    if (subscriptionPlans.products[product]) {
      if (subscriptionPlans.products[product].plans[plan]) {
        subscriptionPlans.products[product].plans[plan].quantity += parseInt(formData.get('plan-quantity'));
      } else {
        subscriptionPlans.products[product].plans[plan] = planObject;
      }
    } else {
      subscriptionPlans.products[product] = {
        id: product,
        name: productName,
        plans: {
          [plan]: planObject
        }
      };
    }

    // console.log(subscriptionPlans);
    renderSubscriptionItems(subscriptionPlans);
    subscriptionPlansInput.value = JSON.stringify(subscriptionPlans);

    addPlanForm.classList.remove('was-validated');
    let modal = new bootstrap.Modal(document.getElementById('add-plan-modal'));
    modal.hide();

    // Reset form
    document.getElementById('product-select').value = '';
    document.getElementById('plan-select').value = '';
    document.getElementById('plan-quantity').value = '1';
  });

  let startsRadios = document.getElementById('subscription-first-bills-radios');
  startsRadios.addEventListener('change', ev => {
    let customDate = document.getElementById('subscription-first-bills-date');
    let customDateBox = document.getElementById('custom-date-box');

    if (ev.target.value == 'custom') {
      customDateBox.classList.remove('d-none');
      customDate.required = true;
    } else {
      customDateBox.classList.add('d-none');
      customDate.required = false;
    }
  });

  let box = document.getElementById('subscription-plans-box');

  box.addEventListener('change', ev => {
    if (ev.target.dataset.formType && ev.target.dataset.formType == 'quantity') {
      if (ev.target.value == 0) {
        // Remove
        delete subscriptionPlans.products[ev.target.dataset.product].plans[ev.target.dataset.plan];
        if (Object.keys(subscriptionPlans.products[ev.target.dataset.product].plans).length == 0) {
          delete subscriptionPlans.products[ev.target.dataset.product];
        }
      } else {
        subscriptionPlans.products[ev.target.dataset.product].plans[ev.target.dataset.plan].quantity = parseInt(ev.target.value);
      }
    }

    renderSubscriptionItems(subscriptionPlans);
    subscriptionPlansInput.value = JSON.stringify(subscriptionPlans);
  });



}