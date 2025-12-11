/*
  Requirement: Make the "Manage Assignments" page interactive.

  Instructions:
  1. Link this file to `admin.html` using:
     <script src="admin.js" defer></script>
  
  2. In `admin.html`, add an `id="assignments-tbody"` to the <tbody> element
     so you can select it.
  
  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// This will hold the assignments loaded from the JSON file.
let assignments = [];

// --- Element Selections ---
// TODO: Select the assignment form ('#assignment-form').
const assignmentForm = document.getElementById("assignment-form");

// TODO: Select the assignments table body ('#assignments-tbody').
const  assignmentsTableBody = document.getElementById("assignments-tbody");

// --- Functions ---

/**
 * TODO: Implement the createAssignmentRow function.
 * It takes one assignment object {id, title, dueDate}.
 * It should return a <tr> element with the following <td>s:
 * 1. A <td> for the `title`.
 * 2. A <td> for the `dueDate`.
 * 3. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and `data-id="${id}"`.
 * - A "Delete" button with class "delete-btn" and `data-id="${id}"`.
 */
function createAssignmentRow(assignment)
{
  //create table row:
  const tr = document.createElement("tr");
  //create td for title and add it to tr:
  const titleTd = document.createElement("td");
  titleTd.textContent = assignment.title;
  tr.appendChild(titleTd);
  //create td for due date and add it to tr:
  const dueDateTd = document.createElement("td");
  dueDateTd.textContent = assignment.dueDate;
  tr.appendChild(dueDateTd);
  //create td for buttons:
  const twoButtons = document.createElement("td");
  //Edit button:
  const editButton = document.createElement("button");
  editButton.textContent = "Edit";
  editButton.className = "edit-btn";
  editButton.setAttribute("data-id", assignment.id);
  //Delete button:
  const deleteButton = document.createElement("button");
  deleteButton.textContent = "Delete";
  deleteButton.className = "delete-btn";
  deleteButton.setAttribute("data-id", assignment.id);
  // add edit and delete buttons to table data:
  twoButtons.appendChild(editButton);
  twoButtons.appendChild(deleteButton);
  //add edit and delete buttons to table row:
  tr.appendChild(twoButtons);
  //return table row:
  return tr;
}
/**
 * TODO: Implement the renderTable function.
 * It should:
 * 1. Clear the `assignmentsTableBody`.
 * 2. Loop through the global `assignments` array.
 * 3. For each assignment, call `createAssignmentRow()`, and
 * append the resulting <tr> to `assignmentsTableBody`.
 */
function renderTable()
{
  //clear assignmentsTableBody:
  assignmentsTableBody.innerHTML = "";
  //loop through assignments:
  assignments.forEach(assignment =>
    {
     //create and append row for each assignment:
     const row = createAssignmentRow(assignment);
     assignmentsTableBody.appendChild(row);
    });
}
/**
 * TODO: Implement the handleAddAssignment function.
 * This is the event handler for the form's 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the values from the title, description, due date, and files inputs.
 * 3. Create a new assignment object with a unique ID (e.g., `id: \`asg_${Date.now()}\``).
 * 4. Add this new assignment object to the global `assignments` array (in-memory only).
 * 5. Call `renderTable()` to refresh the list.
 * 6. Reset the form.
 */
function handleAddAssignment(event)
{
  //prevent default submit:
  event.preventDefault();
  //Get values from title, description, due date, and files inputs:
  const titleInput = document.getElementById("assignment-title");
  const descriptionInput = document.getElementById("assignment-description");
  const dueDateInput = document.getElementById("assignment-due-date");
  const filesInput = document.getElementById("assignment-files");
  const title = titleInput.value;
  const description = descriptionInput.value;
  const dueDate = dueDateInput.value;
  const files = filesInput.files;
  //create new assignment object with unique ID:
   const newAssignment ={
    id: `asg_${Date.now()}`,
    title: title,
    description: description,
    dueDate: dueDate,
    files: files
  };
  //add new assignment (push):
  assignments.push(newAssignment);
  //refresh the list:
   renderTable();
  //Reset form:
  event.target.reset();
}
/**
 * TODO: Implement the handleTableClick function.
 * This is an event listener on the `assignmentsTableBody` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `assignments` array by filtering out the assignment
 * with the matching ID (in-memory only).
 * 4. Call `renderTable()` to refresh the list.
 */
function handleTableClick(event)
{
  //Check if target is clicked and  get data-id if so:
  if (event.target.classList.contains("delete-btn"))
    {
     const id = event.target.getAttribute("data-id");
     //filtering out assignment to update:
     assignments = assignments.filter(assignment => assignment.id !== id);
     //refresh list:
     renderTable();
    }
}
/**
 * TODO: Implement the loadAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'assignments.json'.
 * 2. Parse the JSON response and store the result in the global `assignments` array.
 * 3. Call `renderTable()` to populate the table for the first time.
 * 4. Add the 'submit' event listener to `assignmentForm` (calls `handleAddAssignment`).
 * 5. Add the 'click' event listener to `assignmentsTableBody` (calls `handleTableClick`).
 */
async function loadAndInitialize()
{
  //Get data using fetch():
   const response = await fetch("assignments.json");
  //parse response and store it in assignments array:
   assignments = await response.json();
  //call renderTable:
   renderTable();
  //submit event listener:
  assignmentForm.addEventListener("submit", handleAddAssignment);
  //clik event listener:
  assignmentsTableBody.addEventListener("click", handleTableClick);
}
// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
