import Sortable from "sortablejs";

// let list = document.getElementById("sort-list");
let lists = [];

// Loop through each nested sortable element
for (var i = 0; i < lists.length; i++) {
  new Sortable(lists[i], {
    group: "nested",
    animation: 150,
    fallbackOnBody: true,
    swapThreshold: 0.65
  });
}

