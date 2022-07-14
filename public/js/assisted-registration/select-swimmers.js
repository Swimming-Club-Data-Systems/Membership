let form = document.getElementById('select-form');

form.addEventListener('change', ev => {
  if (ev.target.dataset.collapse) {
    let collapseBox = document.getElementById(ev.target.dataset.collapse)
    let collapse = new bootstrap.Collapse(collapseBox,
      {
        toggle: false
      });
    if (ev.target.checked) {
      collapse.show();
      console.log('SHOW')
      // var bsCollapse = new bootstrap.Collapse(myCollapse, {
      //   show: true
      // })
    } else {
      collapse.hide();
      console.log('HIDE')
      // var bsCollapse = new bootstrap.Collapse(myCollapse, {
      //   hide: true
      // })
    }

  }
});