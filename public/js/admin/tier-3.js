let feeForm = document.getElementById('fee-form');
feeForm.addEventListener('change', ev => {
  calcDiscount(ev.target);
});

function calcDiscount(discount) {
  try {
    let target = document.getElementById(discount.dataset.target);
    let feeAmount = (new BigNumber(discount.dataset.fee)).decimalPlaces(2);
    let discountAmount = (new BigNumber(discount.value)).decimalPlaces(2);
    let amount = feeAmount.minus(discountAmount);
    let amountString = amount.toFormat(2);
    target.value = amountString;
  } catch (error) {
  }
}

let discounts = document.getElementsByClassName('discount-price-boxes');
for (let discount of discounts) {
  calcDiscount(discount);
}