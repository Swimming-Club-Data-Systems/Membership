// let collapseElementList = [].slice.call(document.querySelectorAll('.collapse'))
// let collapseList = collapseElementList.map(function (collapseEl) {
//   return new bootstrap.Collapse(collapseEl)
// })


let form = document.getElementById('form');

form.addEventListener('change', ev => {
  if (ev.target.dataset.type === 'membership-check') {
    // Show/hide collapse
    var myCollapse = document.getElementById(ev.target.dataset.collapseTarget);
    var bsCollapse = new bootstrap.Collapse(myCollapse, {
      toggle: true
    });

  }
})