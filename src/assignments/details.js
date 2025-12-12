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
const textArea = document.getElementById("new-comment-text");

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
  // Get the query string:
  const params = new URLSearchParams(window.location.search);
  //Get value of the 'id' parameter:
  $assignmentId = $_GET['id'] ?? null;
  const id = params.get('id');
  //return id and make sure it is number not string:
  return parseInt(id);
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
  //set textContent to assignment details:
  assignmentTitle.textContent = assignment.title;
  assignmentDueDate.textContent = "Due: " + assignment.due_date;
  assignmentDescription.textContent = assignment.description;
  //clear file list:
  assignmentFilesList.textContent = "";
  // append file with anchor tag and its link for each file of assignment to files array:
  const filesArray = Array.isArray(assignment.files) ? assignment.files : (assignment.files ? assignment.files.split(',') : []);
  filesArray.forEach(fileName => 
  {
    //create list and anchor tags:
    const list = document.createElement("li");
    const anchor = document.createElement("a");
    //set textContnet for each:
    anchor.textContent = fileName;
    anchor.href = "#";
    //anchor.target="_blank"; if upload files were real.
    //append file in list:
    list.append(anchor);
    //append list to files:
    assignmentFilesList.append(list);
  });
}

/**
 * TODO: Implement the createCommentArticle function.
 * It takes one comment object {author, text}.
 * It should return an <article> element matching the structure in `details.html`.
 */
function createCommentArticle(comment)
{
  // Destructure commnet object for easier access:
  const { author, text } = comment;
  //create article,paragraphs, headings for details:
  const article = document.createElement("article");
  const commentText = document.createElement("p")
  const footer = document.createElement("footer");
  //set content of  details:
  commentText.textContent = text;
  footer.textContent = "By: " + author;
  //append div -> comment + footer, article -> div:
  article.append(commentText,footer);
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
  commentList.textContent = "";
  //loop through currentComments array:
  currentComments.forEach(comment =>
    {
      //call createCommentArticle for each comment:
      const newCommentText = createCommentArticle(comment);
      //append each comment to commentList:
       commentList.appendChild(newCommentText);
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
  //Get text: Select and get value
  const commentText = textArea.value;
  //check if text is empty:
  if(commentText===""){return;};
  //create new comment object:
  const newCommentObject = {author: 'Student', text: commentText};
  //add newCommentObject to currentComments array:
  currentComments.push(newCommentObject);
  //Refresh list:
  renderComments();
  //clear textarea:
  textArea.value = "";
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
  //currentAssignmentId:
  currentAssignmentId = getAssignmentIdFromURL();
  //Error handeling:
  if(currentAssignmentId===null){console.log("Error! No ID found."); return;};
  //fetch:
  try{
    const [assignmentsResponse, commentsResponse] = await Promise.all([fetch(`/api/assignments?resource=assignments&id=${currentAssignmentId}`), fetch(`/api/assignments?resource=comments&id=${currentAssignmentId}`)]);
    if (!assignmentsResponse.ok || !commentsResponse.ok) 
      {
        throw new Error('Failed to fetch data from the API.');
      }
    //parse(converting json to object):
    //const [assignments, comments] = await Promise.all([assignmentsResponse.json(), commentsResponse.json()]);
    //find correct assignment and comments from the assignments array using the `currentAssignmentId`:
     // option 1:assignment = assignments[currentAssignmentId];
     //option2: const assignment= assignments.find(a => a.id === currentAssignmentId);
     const assignment = await assignmentsResponse.json();
     const comment= await commentsResponse.json();
     //Store in comments in `currentComments`
     //currentComments = comments[currentAssignmentId];
     currentComments = comment.comments || [];
     //If assignment is found:
  if(assignment && typeof assignment === 'object' && Object.keys(assignment).length > 0)
    {
      //show asssignment details:
      renderAssignmentDetails(assignment);
      //show assignment comments:
      renderComments(currentComments);
      //event listener to `commentForm`:
      commentForm.addEventListener("submit", handleAddComment);
    }
    //If assignment not found display an error:
    else{console.log("Error! No assignment found.");};
  }
  catch (error) {
        console.error("Failed:", error);
    }
}

// --- Initial Page Load ---
initializePage();