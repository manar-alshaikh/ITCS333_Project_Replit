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
const assignmentTableBody = document.getElementById("assignments-tbody");

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
  // Destructure assignment object for easier access:
  const { id, title, due_date} = assignment;
  //create tr and tds:
  const tr = document.createElement("tr");
  const tdtitle = document.createElement("td");
  const tdDueDate = document.createElement("td");
  const tdButtons = document.createElement("td");
  const editButton = document.createElement("button");
  const deleteButton = document.createElement("button");
  //assigning data to each new element:
  tdtitle.textContent = `${title}`
  tdDueDate.textContent= `${due_date}`;
  editButton.textContent = "Edit";
  editButton.className = "edit-btn";
  editButton.dataset.id=`${id}`;
  deleteButton.textContent = "Delete";
  deleteButton.className = "delete-btn";
  deleteButton.dataset.id=`${id}`;
  //append buttons in their td:
  tdButtons.append(editButton, deleteButton);
  //append elements to tr:
  tr.append(tdtitle, tdDueDate, tdButtons);
  //return
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
  assignmentTableBody.innerHTML = "";
  //loop through assignments array:
  assignments.forEach(assignment =>
    {
       //calling createAssignmentRow()
       const tableRow = createAssignmentRow(assignment);
       //append tr:
       assignmentTableBody.appendChild(tableRow);
    })
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
async function handleAddAssignment(event)
{
  //prevent default submission:
  event.preventDefault();
  //Get the values from the title, description, due date, and files inputs:
  const title = document.getElementById("assignment-title").value;
  const description = document.getElementById("assignment-description").value;
  const due_date = document.getElementById("assignment-due-date").value;
  const files = document.getElementById("assignment-files").value;
  /*//create new assignmet object:
  const assignment = {id: Date.now(), title: title, description: description, dueDate: dueDate, files: [file] }
  //add new assignment to assignmets array:
  assignments.push(assignment);*/
  const newAssignmentData = {title: title,description: description, due_date: due_date, files: files};
  const response = await fetch("/assignments/api/index.php?resource=assignments", {method: 'POST',headers: {'Content-Type': 'application/json'},body: JSON.stringify(newAssignmentData)});
  const result = await response.json();
  if (result.success && result.assignment)
    {
      assignments.push(result.assignment);
      //Refresh list:
        renderTable();
      //Reset the form:
        event.target.reset();
    }
  else 
    {
        console.error("Failed to create assignment:", result.error || result.message);
    }
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
async function handleTableClick(event)
{
  //Check if the clicked element (`event.target`) has the class "delete-btn":
  if(event.target.className==="delete-btn")
    {
      //get `data-id`
      const assignmentId = event.target.dataset.id;
      //filtering out the assignment with the matching ID:assignments = assignments.filter(a => a.id !==id);
      const response = await fetch(`/assignments/api/index.php?resource=assignments&id=${assignmentId}`, { method: 'DELETE'});
      const result = await response.json();
      if (result.success)
        {
          assignments = assignments.filter(a => String(a.id) !== assignmentId);
          //refresh list:
           renderTable();
           //alert(result.message);
        }
        else
        {
           console.error("Failed to delete assignment:", result.error || result.message);
           alert("Error deleting assignment: " + (result.error || result.message));
        }
    }
    else if (event.target.classList.contains("edit-btn"))
    {
    const assignmentId = event.target.dataset.id;

    if (assignmentId) {
    // Construct the URL to redirect to update.html, passing the ID as a query parameter
    const redirectUrl = `/assignments/update?id=${assignmentId}`;

    // Perform the redirection
    window.location.href = redirectUrl;
    } else {
    console.error("Edit button clicked but no assignment ID found.");
    }
    }
  else
  {
      console.log(`Assignment not found.`);
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
  //get data from assignments.json using fetch:const data = await fetch("assignments.json");
  const response = await fetch("/assignments/api/index.php?resource=assignments");
  //parse:data = await data.json();
  try
  {
   const result = await response.json();
   if (response.ok)
    {
         //store:assignments = result; 
         assignments = Array.isArray(result) ? result : [];
         //populate:
          renderTable();
    }
   else
   {
     console.error("Failed to load assignments:", result.error || response.statusText);
   }
  } 
  catch (e) {console.error("Error:", e);}
  //submit eventListener:
  assignmentForm.addEventListener("submit", handleAddAssignment);
  //click eventListener:
  assignmentTableBody.addEventListener("click", handleTableClick);
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();