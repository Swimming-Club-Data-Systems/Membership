export function renderSubscriptionItems(subscriptionPlans, target = 'subscription-plans-box') {
  let box = document.getElementById(target);

  let table = document.createElement('TABLE');
  table.classList.add('table');

  let thead = document.createElement('THEAD');
  let tr = document.createElement('TR');

  let th = document.createElement('TH');
  th.setAttribute('scope', 'col');
  th.textContent = 'Product'
  tr.appendChild(th);

  th = document.createElement('TH');
  th.setAttribute('scope', 'col');
  th.textContent = 'Quantity'
  tr.appendChild(th);

  th = document.createElement('TH');
  th.setAttribute('scope', 'col');
  th.textContent = 'Total'
  tr.appendChild(th);

  thead.appendChild(tr);
  table.appendChild(thead);

  let tbody = document.createElement('TBODY');

  Object.keys(subscriptionPlans.products).forEach(function (key) {
    Object.keys(subscriptionPlans.products[key].plans).forEach(function (planId) {

      let product = subscriptionPlans.products[key];
      let plan = subscriptionPlans.products[key].plans[planId];
      // console.table('Product: ' + product.name + ' (' + product.id + '), Plan: ' + plan.name + ' (' + plan.id + '), Price: ' + plan.price_per_unit + ', Quantity: ' + plan.quantity);

      tr = document.createElement('TR');
      tr.id = 'plan-row-' + plan.id;

      let td = document.createElement('TD');
      td.classList.add('align-middle');
      {
        let p1 = document.createElement('P');
        let strong = document.createElement('STRONG');
        strong.textContent = product.name;
        p1.appendChild(strong);
        p1.classList.add('mb-0');

        let p2 = document.createElement('P');
        p2.textContent = plan.name;
        p2.classList.add('mb-0'), 'text-muted';

        td.appendChild(p1);
        td.appendChild(p2);
      }
      tr.appendChild(td);

      td = document.createElement('TD');
      td.classList.add('align-middle');
      {
        let input = document.createElement('INPUT');
        input.setAttribute('name', 'quantity-plan-' + plan.id);
        input.setAttribute('id', 'quantity-plan-' + plan.id);
        input.setAttribute('aria-label', 'Quantity');
        input.setAttribute('type', 'number');
        input.setAttribute('min', '0');
        input.setAttribute('step', '1');
        input.required = true;
        input.value = plan.quantity;
        input.classList.add('form-control');
        input.dataset.product = product.id;
        input.dataset.plan = plan.id;
        input.dataset.formType = 'quantity';
        td.appendChild(input);
      }
      tr.appendChild(td);

      td = document.createElement('TD');
      td.classList.add('align-middle');
      {
        td.textContent = (new BigNumber((plan.price_per_unit * plan.quantity))).shiftedBy(-2).decimalPlaces(2).toFormat(2) + ' ' + plan.currency.toUpperCase();
      }
      tr.appendChild(td);

      tbody.appendChild(tr);
    })
  });

  table.appendChild(tbody);

  box.innerHTML = '';
  box.appendChild(table);
}