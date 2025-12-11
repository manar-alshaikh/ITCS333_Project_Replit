/*
  Requirement: Populate the assignment detail page and discussion forum.

  Instructions:
  1. Link this file to `details.html` using:
     <script src="details.js" defer></script>

  2. In `details.html`, add the following IDs:
     - To the <h1>: `id="assignment-title"`
     - To the "Due" <p>: `id="assignment-due-date"`
     - To the "Description" <p>: `id="assignment-description"`
     - To the "Attached Files" <ul>: `id="assignment-files-list"`
     - To the <div> for comments: `id="comment-list"`
     - To the "Add a Comment" <form>: `id="comment-form"`
     - To the <textarea>: `id="new-comment-text"`

  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// These will hold the data related to *this* assignment.
let currentAssignmentId = null;
let currentComments = [];

// --- Element Selections ---
// TODO: Select all the elements you added IDs for in step 2.
const assignmentTitle = document.getElementById("assignment-title");
const assignmentDueDate = document.getElementById("assignment-due-date");
const assignmentDescription = document.getElementById("assignment-description");
const assignmentFilesList = document.getElementById("assignment-files-list");
const commentList = document.getElementById("comment-list");
const commentForm = document.getElementById("comment-form");
const newCommentText = document.getElementById("new-comment-text");
// --- Functions ---

/**
 * TODO: Implement the getAssignmentIdFromURL function.
 * It should:
 * 1. Get the query string from `window.location.search`.
 * 2. Use the `URLSearchParams` object to get the value of the 'id' parameter.
 * 3. Return the id.
 */
function getAssignmentIdFromURL()
{ 
  //Get the query string (qs):
  const qs = window.location.search;
  //Get value of id - aid-> assignment id, usp-> url srearch param:
  const usp = new URLSearchParams(qs);
  const aid = usp.get("id");
  //Return id
  return aid;
}
/**
 * TODO: Implement the renderAssignmentDetails function.
 * It takes one assignment object.
 * It should:
 * 1. Set the `textContent` of `assignmentTitle` to the assignment's title.
 * 2. Set the `textContent` of `assignmentDueDate` to "Due: " + assignment's dueDate.
 * 3. Set the `textContent` of `assignmentDescription`.
 * 4. Clear `assignmentFilesList` and then create and append
 * `<li><a href="#">...</a></li>` for each file in the assignment's 'files' array.
 */
function renderAssignmentDetails(assignment)
{
  // set textContent:
  assignmentTitle.textContent = assignment.title;
  assignmentDueDate.textContent = "Due: " + assignment.dueDate;
  assignmentDescription.textContent = assignment.description;
  // clear 
  assignmentFilesList.innerHTML = "";
  // loop through each file
  assignment.files.forEach(file => {
    //create new list item and new link:
    const li = document.createElement("li");
    const a = document.createElement("a");
    //set link destination and add it to list item:
    a.href = "#";
    a.textContent = file;
    li.appendChild(a);
    //add list item to files list:
    assignmentFilesList.appendChild(li);
  });
}

/**
 * TODO: Implement the createCommentArticle function.
 * It takes one comment object {author, text}.
 * It should return an <article> element matching the structure in `details.html`.
 */
function createCommentArticle(comment) 
{
  //create article:
  const article = document.createElement("article");
  //create author in a paragraph and add it to article:
  const author = document.createElement("footer");
  author.textContent = `Author: ${comment.author}`;
  article.appendChild(author);
  //create text in paragraph and add it to article:
  const text =  document.createElement("p");
  text.textContent = comment.text;
  article.appendChild(text);
  //return article:
  return article;
}
/**
 * TODO: Implement the renderComments function.
 * It should:
 * 1. Clear the `commentList`.
 * 2. Loop through the global `currentComments` array.
 * 3. For each comment, call `createCommentArticle()`, and
 * append the resulting <article> to `commentList`.
 */
function renderComments()
{
  //clear commentList:
  commentList.innerHTML = "";
  //loop through currentComments:
  currentComments.forEach(comment =>{
    //create and add comment:
    const commentArticle = createCommentArticle(comment);
    commentList.appendChild(commentArticle);
  });
}
/**
 * TODO: Implement the handleAddComment function.
 * This is the event handler for the `commentForm` 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the text from `newCommentText.value`.
 * 3. If the text is empty, return.
 * 4. Create a new comment object: { author: 'Student', text: commentText }
 * (For this exercise, 'Student' is a fine hardcoded author).
 * 5. Add the new comment to the global `currentComments` array (in-memory only).
 * 6. Call `renderComments()` to refresh the list.
 * 7. Clear the `newCommentText` textarea.
 */
function handleAddComment(event)
{
  //prevent default submission:
  event.preventDefault();
  //Get text:
  const commentText = newCommentText.value;
  //retrun if empty:
  if(commentText === ""){return;};
  //create new comment and add it:
  const newComment = {
    author: "Student",
    text: commentText
  };
  currentComments.push(newComment);
  //Refresh list:
  renderComments();
  //clear textarea:
  newCommentText.value = "";
}

/**
 * TODO: Implement an `initializePage` function.
 * This function needs to be 'async'.
 * It should:
 * 1. Get the `currentAssignmentId` by calling `getAssignmentIdFromURL()`.
 * 2. If no ID is found, display an error and stop.
 * 3. `fetch` both 'assignments.json' and 'comments.json' (you can use `Promise.all`).
 * 4. Find the correct assignment from the assignments array using the `currentAssignmentId`.
 * 5. Get the correct comments array from the comments object using the `currentAssignmentId`.
 * Store this in the global `currentComments` variable.
 * 6. If the assignment is found:
 * - Call `renderAssignmentDetails()` with the assignment object.
 * - Call `renderComments()` to show the initial comments.
 * - Add the 'submit' event listener to `commentForm` (calls `handleAddComment`).
 * 7. If the assignment is not found, display an error.
 */
async function initializePage()
{
  //Get currentAssignmentId:
  currentAssignmentId = getAssignmentIdFromURL();
  //Dispaly error if ID not found:
  if (!currentAssignmentId) 
   {
    console.error("Error: No assignment ID found in URL.");
    return;
   };
   //fetch:
   try 
   {
    const [assignmentsResponse, commentsResponse] = await Promise.all([
      fetch("assignments.json"),
      fetch("comments.json")
    ]);
    const assignments = await assignmentsResponse.json();
    const commentsData = await commentsResponse.json();
    //find correct assignment and comments:
    const assignment = assignments.find(a => String(a.id) === String(currentAssignmentId));
    currentComments = commentsData[currentAssignmentId] || [];
    //if assignment found call render, show assignemnt details and initial comments:
    if (assignment) {
      renderAssignmentDetails(assignment);  
      renderComments();
      //enable adding comments:                
      commentForm.addEventListener("submit", handleAddComment);
    } else {
      //Display error if assignment not found:
      console.error("Error: Assignment not found for ID " + currentAssignmentId);
    }
  }
  //handeling error:
  catch (error) 
  {
    console.error("Error loading data:", error);
  }
}
// --- Initial Page Load ---
initializePage();
