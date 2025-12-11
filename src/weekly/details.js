


let currentWeekId = null;
let currentComments = [];
let weekData = null;


const weekTitle = document.getElementById("week-title");
const weekStartDate = document.getElementById("week-start-date");
const weekDescription = document.getElementById("week-description");
const weekLinksList = document.getElementById("week-links-list");
const commentList = document.getElementById("comment-list");
const commentForm = document.getElementById("comment-form");
const newCommentText = document.getElementById("new-comment-text");


const cache = {
    week: null,
    comments: null,
    timestamp: 0,
    CACHE_DURATION: 30000,

    setWeek(data) {
        this.week = data;
        this.timestamp = Date.now();
    },

    getWeek() {
        if (this.week && Date.now() - this.timestamp < this.CACHE_DURATION) {
            return this.week;
        }
        return null;
    },

    clear() {
        this.week = null;
        this.comments = null;
        this.timestamp = 0;
    }
};


function getWeekIdFromURL() {
    return new URLSearchParams(window.location.search).get("id");
}

function formatDateTime(date) {
    const d = new Date(date);
    return `${d.getMonth()+1}/${d.getDate()} ${d.getHours()}:${d.getMinutes().toString().padStart(2, '0')}`;
}

function getInitials(name = "") {
    if (!name) return "U";
    const parts = name.trim().split(" ");
    return ((parts[0]?.[0] || "") + (parts[1]?.[0] || "")).toUpperCase();
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}


async function deleteComment(id) {
    try {
        const res = await fetch(`./api/index.php?action=delete_comment&id=${id}`, {
            method: "DELETE"
        });
        const data = await res.json();
        return data.success;
    } catch (e) {
        console.error("Delete API failed:", e);
        return false;
    }
}


function renderWeekDetails(week) {
    requestAnimationFrame(() => {
        weekTitle.textContent = week.title || "";
        weekStartDate.textContent = week.start_date ? `Starts on: ${week.start_date}` : "";
        weekDescription.textContent = week.description || "";

        if (Array.isArray(week.links) && week.links.length > 0) {
            const fragment = document.createDocumentFragment();
            week.links.forEach(link => {
                const li = document.createElement("li");
                const a = document.createElement("a");
                a.href = link;
                a.target = "_blank";
                a.textContent = link.length > 50 ? link.substring(0, 50) + "..." : link;
                li.appendChild(a);
                fragment.appendChild(li);
            });
            weekLinksList.innerHTML = "";
            weekLinksList.appendChild(fragment);
        } else {
            weekLinksList.innerHTML = "";
        }
    });
}


function createReplyHTML(reply) {
    return `
        <div class="reply">
           <div class="reply-header">
    <div class="reply-header-left">
        <div class="reply-icon">${getInitials(reply.username)}</div>
        <div class="reply-author">${escapeHtml(reply.username)}</div>
        <div class="reply-timestamp">${formatDateTime(reply.created_at)}</div>
    </div>

    ${window.IS_ADMIN ? `<button class="delete-reply-btn" data-id="${reply.id}">🗑</button>` : ""}
</div>

            <p>${escapeHtml(reply.comment_text)}</p>
        </div>
    `;
}


function createCommentElement(comment) {
    const article = document.createElement("article");
    article.className = "comment";
    article.dataset.commentId = comment.id;

    article.innerHTML = `
<div class="comment-header">
    <div class="comment-header-left">
        <div class="comment-icon">${getInitials(comment.username)}</div>
        <div class="comment-author">${escapeHtml(comment.username)}</div>
        <div class="comment-timestamp">${formatDateTime(comment.created_at)}</div>
    </div>

    ${window.IS_ADMIN ? `<button class="delete-comment-btn" data-id="${comment.id}">🗑</button>` : ""}
</div>


        <p>${escapeHtml(comment.comment_text)}</p>

        <div class="nested-replies" ${comment.replies?.length ? "" : 'style="display:none"'} >
            ${comment.replies?.map(r => createReplyHTML(r)).join("") || ""}
        </div>

        <form class="reply-form" data-comment-id="${comment.id}">
            <textarea class="reply-textarea" rows="2" placeholder="Write a reply..." required></textarea>
            <button type="submit" class="reply-btn">Send</button>
        </form>
    `;

    const replyForm = article.querySelector(".reply-form");
    replyForm.addEventListener("submit", (e) => handleReplySubmit(e, comment.id));

    return article;
}


function renderComments() {
    commentList.innerHTML = "";

    if (!currentComments.length) {
        commentList.innerHTML = `<p style="text-align:center; opacity:0.7">No comments yet.</p>`;
        return;
    }

    const fragment = document.createDocumentFragment();
    currentComments.forEach(c => fragment.appendChild(createCommentElement(c)));
    commentList.appendChild(fragment);
}


async function handleReplySubmit(e, parentCommentId) {
    e.preventDefault();
    const form = e.target;
    const textArea = form.querySelector(".reply-textarea");
    const text = textArea.value.trim();

    if (!text) return;

    const payload = {
        week_id: Number(currentWeekId),
        user_id: Number(window.LOGGED_IN_USER_ID),
        comment_text: text,
        parent_comment_id: parentCommentId
    };

    const res = await fetch("./api/index.php?action=weekly_comments", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (data.success) {
        textArea.value = "";
        await loadComments();
    }
}


async function handleAddComment(e) {
    e.preventDefault();

    const text = newCommentText.value.trim();
    if (!text) return;

    const payload = {
        week_id: Number(currentWeekId),
        user_id: Number(window.LOGGED_IN_USER_ID),
        comment_text: text
    };

    const res = await fetch("./api/index.php?action=weekly_comments", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (data.success) {
        newCommentText.value = "";
        await loadComments();
    }
}


async function loadWeek() {
    const cached = cache.getWeek();
    if (cached) {
        weekData = cached;
        renderWeekDetails(weekData);
        return;
    }

    const res = await fetch(`./api/index.php?id=${currentWeekId}`);
    const data = await res.json();

    if (data.success) {
        weekData = data.data;
        cache.setWeek(weekData);
        renderWeekDetails(weekData);
    }
}


async function loadComments() {
    const res = await fetch(`./api/index.php?resource=weekly_comments&week_id=${currentWeekId}`);
    const data = await res.json();

    if (!data.success) {
        currentComments = [];
        renderComments();
        return;
    }

    const flat = data.data;
    const map = new Map();
    flat.forEach(c => {
        c.replies = [];
        map.set(c.id, c);
    });

    const roots = [];
    flat.forEach(c => {
        if (c.parent_comment_id) {
            map.get(c.parent_comment_id)?.replies.push(c);
        } else roots.push(c);
    });

    currentComments = roots;
    renderComments();
}


document.addEventListener("click", async (e) => {
    if (e.target.classList.contains("delete-comment-btn")) {
        const id = e.target.dataset.id;
        if (confirm("Delete this comment?")) {
            if (await deleteComment(id)) loadComments();
        }
    }

    if (e.target.classList.contains("delete-reply-btn")) {
        const id = e.target.dataset.id;
        if (confirm("Delete this reply?")) {
            if (await deleteComment(id)) loadComments();
        }
    }
});


async function initializePage() {
    currentWeekId = getWeekIdFromURL();
    if (!currentWeekId) {
        weekTitle.textContent = "Week not found";
        return;
    }

    await Promise.all([loadWeek(), loadComments()]);

    if (!commentForm._attached) {
        commentForm.addEventListener("submit", handleAddComment);
        commentForm._attached = true;
    }
}

document.addEventListener("DOMContentLoaded", initializePage);
