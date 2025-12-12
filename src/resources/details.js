/*
  details.js - Database Connected Version
*/

// --- Global Data ---
let currentResourceId = null;
let currentComments = [];

// --- Element Selectors ---
const resourceTitle = document.querySelector("#resource-title");
const resourceDescription = document.querySelector("#resource-description");
const resourceLink = document.querySelector("#resource-link");

const commentList = document.querySelector("#comment-list");
const commentForm = document.querySelector("#comment-form");
const newComment = document.querySelector("#new-comment-text");

// API URL
const API_URL = "/resources/api/index.php";

function getResourceIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

function renderResourceDetails(resource) {
  resourceTitle.textContent = resource.title;
  resourceDescription.textContent = resource.description;
  resourceLink.href = resource.external_url;
}

function createCommentArticle(comment) {
  const article = document.createElement("article");
  article.innerHTML = `
    <p>${comment.comment_text}</p>
    <footer>Posted by: User ${comment.user_id}</footer>
  `;
  return article;
}

function renderComments() {
  commentList.innerHTML = "";
  currentComments.forEach((c) => {
    commentList.appendChild(createCommentArticle(c));
  });
}

async function handleAddComment(event) {
  event.preventDefault();

  const text = newComment.value.trim();
  if (!text) return;

  const bodyData = {
    resource_id: currentResourceId,
    text: text
  };

  const response = await fetch(`${API_URL}?action=comment`, {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify(bodyData)
  });

  const result = await response.json();

  if (result.success) {
    currentComments.push({
      comment_text: text,
      user_id: "You"
    });

    renderComments();
    newComment.value = "";
  } else {
    alert("Error posting comment");
  }
}

async function initializePage() {
  currentResourceId = getResourceIdFromURL();

  if (!currentResourceId) {
    resourceTitle.textContent = "Invalid resource ID.";
    return;
  }

  try {
    const resourceRes = await fetch(`${API_URL}?id=${currentResourceId}`);
    const resourceData = await resourceRes.json();

    if (!resourceData.success) {
      resourceTitle.textContent = "Resource not found.";
      return;
    }

    renderResourceDetails(resourceData.data);

    const commentsRes = await fetch(
      `${API_URL}?action=comments&resource_id=${currentResourceId}`
    );
    const commentsData = await commentsRes.json();

    currentComments = commentsData.data || [];
    renderComments();

    commentForm.addEventListener("submit", handleAddComment);

  } catch (error) {
    console.error(error);
    resourceTitle.textContent = "Error loading resource.";
  }
}

initializePage();

