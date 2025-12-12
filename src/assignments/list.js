/*
  Requirement: Populate the "Course Assignments" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="assignment-list-section"` to the
     <section> element that will contain the assignment articles.

  3. Implement the TODOs below.
*/

// --- Element Selections ---
// TODO: Select the section for the assignment list ('#assignment-list-section').
const assignmentList = document.getElementById("assignment-list-section");

// --- Functions ---
//API:
 const API = "/api/assignments?resource=assignments";
/**
 * TODO: Implement the createAssignmentArticle function.
 * It takes one assignment object {id, title, dueDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * The "View Details" link's `href` MUST be set to `details.html?id=${id}`.
 * This is how the detail page will know which assignment to load.
 */
function createAssignmentArticle(assignment)
{
  // Destructure assignment object for easier access:
  const { id, title, due_date, description } = assignment;
  //creating article element:
  const article = document.createElement("article");
  //creating details:
  const h2 = document.createElement("h2");
  const p1 = document.createElement("p");
  const p2 = document.createElement("p");
  const a = document.createElement("a");
  //assigning data to each new element:
  a.textContent = "View Details";
  a.href=`/assignments/details?id=${id}`;
  h2.textContent = `Assignment ${id}: ${title}`;
  p1.textContent="Due:" + ` ${due_date}`;
  p2.textContent= description;
  //adding element to article:
  article.append(h2, p1, p2, a);
  //return
  return article;
}

/**
 * TODO: Implement the loadAssignments function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'assignments.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the assignments array. For each assignment:
 * - Call `createAssignmentArticle()`.
 * - Append the returned <article> element to `listSection`.
 */
async function loadAssignments()
{
  //fetch
  const response = await fetch(API);
  //parse(converting json to object)
  const assignments = await response.json();
  //clear content from assignment list:
  assignmentList.textContent="";
  //Loop for each assignment:
  assignments.forEach(assignment =>
    {
      //call create article function:
      const newArticle = createAssignmentArticle(assignment);
      //Append article to section:
      assignmentList.appendChild(newArticle);
    });
}

// --- Initial Page Load ---
// Call the function to populate the page.
loadAssignments();