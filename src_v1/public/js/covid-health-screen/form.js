/**
 * Code for COVID-19 Health Survey
 */

function showHideYesCallBack(event) {
  radioShowHide(event);
}

function showHideNoCallBack(event) {
  radioShowHide(event, '0');
}

function radioShowHide(event, showState = '1') {
  let groupName = event.target.parentElement.parentElement.dataset.groupName;
  let moreContainer = document.getElementById(groupName + '-more');
  let moreBox = document.getElementById(groupName + '-more-textarea');

  if (event.target.value === showState) {
    // Show textarea and require content
    moreContainer.classList.remove('d-none');
    moreBox.required = true;

  } else {
    // Hide textarea and remove required
    moreContainer.classList.add('d-none');
    moreBox.required = false;

  }
}

let radios = document.getElementsByClassName('yes-requires-more-radio');
for (let radio of radios) {
  radio.addEventListener('change', showHideYesCallBack);
}

radios = document.getElementsByClassName('no-requires-more-radio');
for (let radio of radios) {
  radio.addEventListener('change', showHideNoCallBack);
}

radios = document.getElementsByName('sought-advice-radio');
for (let radio of radios) {
  radio.addEventListener('change', (event) => {
    let newQuestion = document.getElementById('sought-advice-more');
    let newQuestionRadios = document.getElementsByName('advice-received-radio');

    if (event.target.value === '1') {
      newQuestion.classList.remove('d-none');
      for (let newQuestionRadio of newQuestionRadios) {
        newQuestionRadio.required = true;
      }
    } else {
      newQuestion.classList.add('d-none');
      for (let newQuestionRadio of newQuestionRadios) {
        newQuestionRadio.required = false;
      }
    }
    console.log(event.target);
  });
}