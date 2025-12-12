
let weeks = [];
let currentEditId = null;



const weekForm = document.getElementById("week-form");
const weeksTableBody = document.getElementById("weeks-tbody");
const mainContent = document.getElementById("main-content");

const editModalOverlay = document.getElementById("edit-modal-overlay");
const editForm = document.getElementById("edit-week-form");

const editTitleInput = document.getElementById("edit-week-title");
const editStartDateInput = document.getElementById("edit-week-start-date");
const editDescriptionInput = document.getElementById("edit-week-description");
const editLinksInput = document.getElementById("edit-week-links");
const cancelEditBtn = document.getElementById("cancel-edit");

const globalErrorBox = document.getElementById("form-global-error");



function debounce(func, delay = 800) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => func.apply(this, args), delay);
  };
}



function isValidURL(url) {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}



function showError(input, message) {
  let errorElem = input.nextElementSibling;

  if (!errorElem || !errorElem.classList.contains("error-message")) {
    errorElem = document.createElement("div");
    errorElem.className = "error-message";
    errorElem.style.color = "red";
    errorElem.style.fontSize = "0.85rem";
    errorElem.style.marginTop = "0.2rem";
    input.parentNode.insertBefore(errorElem, input.nextSibling);
  }

  errorElem.textContent = message;
}

function clearError(input) {
  const errorElem = input.nextElementSibling;
  if (errorElem && errorElem.classList.contains("error-message")) {
    errorElem.textContent = "";
  }
}



function showGlobalError(message) {
  globalErrorBox.textContent = message;
  globalErrorBox.style.display = "block";
}

function clearGlobalError() {
  globalErrorBox.textContent = "";
  globalErrorBox.style.display = "none";
}



function validateField(input, type = "text") {
  const value = input.value.trim();

  if (type === "text") {
    if (value.length < 3) {
      showError(input, "This field must contain at least 3 characters.");
      return false;
    }
    clearError(input);
    return true;
  }

  if (type === "date") {
    if (!value) {
      showError(input, "Please select a date.");
      return false;
    }
    clearError(input);
    return true;
  }

  if (type === "links") {
    const links = value
      .split("\n")
      .map(l => l.trim())
      .filter(l => l !== "");

    for (let link of links) {
      if (!isValidURL(link)) {
        showError(input, "One or more links are invalid URLs.");
        return false;
      }
    }
    clearError(input);
    return true;
  }

  return true;
}

function validateForm(fields) {
  let valid = true;
  fields.forEach(({ input, type }) => {
    if (!validateField(input, type)) valid = false;
  });
  return valid;
}



function createWeekRow(week) {
  const row = document.createElement("tr");

  const titleCell = document.createElement("td");
  const link = document.createElement("a");
  link.href = `./details.php?id=${week.id}`;
  link.textContent = week.title;
  link.className = "week-title-link";
  titleCell.appendChild(link);

  const descCell = document.createElement("td");
  descCell.textContent = week.description;

  const actionsCell = document.createElement("td");
  actionsCell.className = "actions";

  const editBtn = document.createElement("button");
  editBtn.textContent = "Edit";
  editBtn.className = "edit-btn";
  editBtn.dataset.id = week.id;

  const deleteBtn = document.createElement("button");
  deleteBtn.textContent = "Delete";
  deleteBtn.className = "delete-btn delete-week-btn";
  deleteBtn.dataset.id = week.id;

  actionsCell.append(editBtn, deleteBtn);
  row.append(titleCell, descCell, actionsCell);

  return row;
}



function renderTable() {
  weeksTableBody.innerHTML = "";
  weeks.forEach(week => weeksTableBody.appendChild(createWeekRow(week)));
}



async function handleAddWeek(event) {
  event.preventDefault();

  const title = document.getElementById("week-title");
  const date = document.getElementById("week-start-date");
  const description = document.getElementById("week-description");
  const links = document.getElementById("week-links");

  const fields = [
    { input: title, type: "text" },
    { input: date, type: "date" },
    { input: description, type: "text" },
    { input: links, type: "links" }
  ];

  if (!validateForm(fields)) {
    showGlobalError("Fix the highlighted fields.");
    return;
  }

  clearGlobalError();

  const nextWeekId =
    weeks.length > 0 ? Math.max(...weeks.map(w => w.week_id)) + 1 : 1;

  const payload = {
    week_id: nextWeekId,
    title: title.value.trim(),
    description: description.value.trim(),
    start_date: date.value.trim(),
    links: links.value.split("\n").map(v => v.trim()).filter(v => v !== "")
  };

  try {
    const res = await fetch("./api/index.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const result = await res.json();

    if (!result.success) {
      showGlobalError(result.error || "Error saving week.");
      return;
    }

    const created = result.data;

    weeks.push({
      id: Number(created.id),
      week_id: Number(created.week_id),
      title: created.title,
      startDate: created.start_date,
      description: created.description,
      links: created.links
    });

    renderTable();
    weekForm.reset();
  } catch {
    showGlobalError("Server error when saving week.");
  }
}



function handleTableClick(event) {
  const target = event.target;

  if (target.classList.contains("delete-btn")) {
    const id = Number(target.dataset.id);

    fetch(`./api/index.php?id=${id}`, { method: "DELETE" })
      .then(res => res.json())
      .then(result => {
        if (!result.success) {
          alert("Delete failed: " + result.error);
          return;
        }

        weeks = weeks.filter(w => w.id !== id);
        renderTable();
      })
      .catch(() => alert("Server error deleting week."));
  }

  if (target.classList.contains("edit-btn")) {
    const id = Number(target.dataset.id);
    const week = weeks.find(w => w.id === id);

    if (!week) return;

    currentEditId = id;

    editTitleInput.value = week.title;
    editStartDateInput.value = week.startDate;
    editDescriptionInput.value = week.description;
    editLinksInput.value = week.links.join("\n");

    editModalOverlay.style.display = "flex";
    mainContent.classList.add("dimmed");
  }
}



async function handleEditSubmit(event) {
  event.preventDefault();

  const fields = [
    { input: editTitleInput, type: "text" },
    { input: editStartDateInput, type: "date" },
    { input: editDescriptionInput, type: "text" },
    { input: editLinksInput, type: "links" }
  ];

  if (!validateForm(fields)) return;


  const oldWeek = weeks.find(w => w.id === currentEditId);

  const payload = {
    id: Number(currentEditId),
    week_id: oldWeek.week_id,
    title: editTitleInput.value.trim(),
    start_date: editStartDateInput.value.trim(),
    description: editDescriptionInput.value.trim(),
    links: editLinksInput.value
      .split("\n")
      .map(v => v.trim())
      .filter(v => v !== "")
  };

  try {
    const res = await fetch("./api/index.php", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const result = await res.json();

    if (!result.success) {
      alert("Update failed: " + result.error);
      return;
    }

    const updated = result.data;

    weeks = weeks.map(w =>
      w.id === updated.id
        ? {
          id: updated.id,
          week_id: updated.week_id,
          title: updated.title,
          startDate: updated.start_date,
          description: updated.description,
          links: updated.links
        }
        : w
    );

    renderTable();
    closeEditModal();

  } catch (e) {
    alert("Server error updating week.");
  }
}



function closeEditModal() {
  editModalOverlay.style.display = "none";
  mainContent.classList.remove("dimmed");
  currentEditId = null;
}



async function importJsonWeeksOnce(jsonWeeks) {
  if (localStorage.getItem("weeksImported") === "yes") {
    console.log("⏭ Import skipped — already done once.");
    return;
  }

  try {
    const res = await fetch("./api/index.php");
    const dbResult = await res.json();

    const existingWeeks = dbResult.success ? dbResult.data : [];
    const existingKeys = new Set();
    let maxWeekId = 0;

    existingWeeks.forEach(w => {
      const key = (w.title || "").toLowerCase().trim() + "|" + (w.start_date || "").trim();
      existingKeys.add(key);

      const wid = Number(w.week_id);
      if (!isNaN(wid) && wid > maxWeekId) maxWeekId = wid;
    });

    let nextWeekId = maxWeekId + 1;

    for (const w of jsonWeeks) {
      const startDate = w.startDate || w.start_date || "";
      const key = (w.title || "").toLowerCase().trim() + "|" + String(startDate).trim();

      if (existingKeys.has(key)) continue;

      const payload = {
        week_id: nextWeekId,
        title: w.title,
        description: w.description,
        start_date: startDate,
        links: w.links || []
      };

      const insertRes = await fetch("./api/index.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      const insertResult = await insertRes.json();

      if (insertResult.success) {
        nextWeekId++;
      }
    }

    localStorage.setItem("weeksImported", "yes");
    console.log("✅ JSON weeks imported once and locked.");
  } catch (err) {
    console.error("Import error:", err);
  }
}


async function importJsonCommentsOnce() {
  if (localStorage.getItem("commentsImported") === "yes") {
    console.log("⏭ Comments already imported once.");
    return;
  }

  try {

    const res = await fetch("./api/comments.json");
    const allComments = await res.json();


    const usersRes = await fetch("./api/index.php?resource=users");
    const usersData = await usersRes.json();

    const users = usersData.success ? usersData.data : [];

    const userMap = {};
    users.forEach(u => {
      if (!u || !u.username) return;
      userMap[u.username.trim().toLowerCase()] = u.id;
    });


    for (const weekKey in allComments) {
      const weekId = Number(weekKey.replace("week_", ""));
      const comments = allComments[weekKey];

      if (!Array.isArray(comments)) continue;

      for (const c of comments) {

        if (!c || !c.author || !c.text) continue;

        const author = c.author.trim().toLowerCase();
        const text = c.text.trim();

        const userId = userMap[author];
        if (!userId) {
          console.warn("⚠ Unknown user, skipping:", c.author);
          continue;
        }


        await fetch("./api/index.php?action=weekly_comments", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            week_id: weekId,
            user_id: userId,
            comment_text: text
          })
        });
      }
    }

    localStorage.setItem("commentsImported", "yes");
    console.log("✅ Comments imported successfully (one time only).");

  } catch (err) {
    console.error("❌ Comments import error:", err);
  }
}





async function loadAndInitialize() {
  try {
    const response = await fetch("./api/weeks.json");
    const jsonWeeks = await response.json();

    await importJsonWeeksOnce(jsonWeeks);
    await importJsonCommentsOnce();

    const res = await fetch("./api/index.php");
    const result = await res.json();

    if (result.success) {
      weeks = result.data.map(w => ({
        id: Number(w.id),
        week_id: Number(w.week_id),
        title: w.title,
        startDate: w.start_date,
        description: w.description,
        links: w.links
      }));
    }
  } catch {
    weeks = [];
  }

  renderTable();

  weekForm.addEventListener("submit", handleAddWeek);
  editForm.addEventListener("submit", handleEditSubmit);
  weeksTableBody.addEventListener("click", handleTableClick);
  cancelEditBtn.addEventListener("click", closeEditModal);
}

loadAndInitialize();

document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("go-back-btn");
  if (!btn) return;

  btn.addEventListener("click", () => {
    window.location.href = "../auth/AdminPortal.php";
  });
});
