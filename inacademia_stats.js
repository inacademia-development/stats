var selectedTab = "";

function submitForm(form) {
  form.elements['tab'].value = selectedTab;
  form.submit();
}

function openTab(evt, tabName) {
  selectedTab = tabName;

  // Declare all variables
  var i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tabName).style.display = "block";
  document.getElementById(tabName + '_tab').className += " active";

  setState(null, tabName, null, null);
}

function openURL(time, tab, filter, interval, sp) {
  setState(time, tab, filter, interval, sp);

  cur_state = getState();

  var loc = String(window.location);
  var res = loc.split("?");

  window.location.replace(res[0] + "?t=" + cur_state["time"] +"&tab=" +cur_state["tab"]+ "&f=" +cur_state["filter"]+ "&p=" +cur_state["interval"] +"&sp=" + cur_state['sp']);
}

function setActive(type, active_type) {
	return (type == active_type) ? "button_selected" :"button";
}

function setState(time, tab, filter, interval, sp) {
  if (time != null) {
    document.getElementById('state_form').elements["time"].value = time;
  }
  if (tab != null) {
    document.getElementById('state_form').elements["tab"].value = tab;
  }
  if (filter != null) {
    document.getElementById('state_form').elements["filter"].value = filter;
  }
  if (interval != null) {
    document.getElementById('state_form').elements["interval"].value = interval;
  }
  if (sp != null) {
    document.getElementById('state_form').elements["sp"].value = sp;
  }
}

function getState() {
  var state = [];

  state["time"] = document.getElementById('state_form').elements["time"].value;
  state["tab"] = document.getElementById('state_form').elements["tab"].value;
  state["filter"] = document.getElementById('state_form').elements["filter"].value;
  state["interval"] = document.getElementById('state_form').elements["interval"].value;
  state["sp"] = document.getElementById('state_form').elements["sp"].value;

  return state;
}

