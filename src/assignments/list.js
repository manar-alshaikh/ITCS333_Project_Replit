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
const assignmentListSection = document.getElementById("assignment-list-section");
// --- Functions ---
/**
 * TODO: Implement the createAssignmentArticle function.
 * It takes one assignment object {id, title, dueDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * The "View Details" link's `href` MUST be set to `details.html?id=${id}`.
 * This is how the detail page will know which assignment to load.
 */
function createAssignmentArticle({ id, title, dueDate, description })
{
  //creating article:
  const article = document.createElement("article");
  // creating title and add it to article:
  const h2Title = document.createElement("h2");
  h2Title.textContent = title;
  article.appendChild(h2Title);
  //creating due date and add it to article in a paragraph:
  const duedate = document.createElement("p");
  duedate.textContent = `Due: ${dueDate}`; 
  article.appendChild(duedate);
  //creating description and add it to article in a paragraph:
  const desc = document.createElement("p");
  desc.textContent = description;
  article.appendChild(desc);
  //creating link to view details by id:
  const link = document.createElement("a");
  link.href = `details.html?id=${id}`;
  link.textContent = "View Detail";
  article.appendChild(link);
  //load:
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
  try 
  {
    //request the JSON file:
    const response = await fetch("assignments.json");
    //converting it into a JavaScript array of objects:
    const assignments = await response.json();
    // Handelling duplicates by cleaning before adding fresh content:
    assignmentListSection.innerHTML = "";
    // 4. Loop through each assignment objects and show then in article:
    assignments.forEach(assignment =>{
        const article = createAssignmentArticle(assignment);
        assignmentListSection.appendChild(article);
    });
    //Handelling error:
  } catch (error) {
    console.error("Error loading assignments:", error);
  }
}
// --- Initial Page Load ---
// Call the function to populate the page.
loadAssignments();