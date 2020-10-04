function showForm(name) {
  var x = document.getElementById(name);
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}

function toggleCheckbox(element, child) {
  if(element.checked){
      document.getElementById(child).checked = true;
  }
  else{
      document.getElementById(child).checked = false;
  }
}

function replace_div(source_div1, target_div1, source_div2, target_div2) {
  document.getElementById(source_div1).innerHTML = document.getElementById(target_div1).innerHTML;
  document.getElementById(source_div2).innerHTML = document.getElementById(target_div2).innerHTML;
}