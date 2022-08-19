let form = document.getElementById('form');
form.addEventListener('change', ev => {

  console.log(ev.target);

  if (ev.target.type === 'radio' && ev.target.dataset.toggle) {

    let toggle = document.getElementById(ev.target.dataset.toggle);

    var bsCollapse = new bootstrap.Collapse(toggle, {
      show: (ev.target.value == '1')
    });
  }

});