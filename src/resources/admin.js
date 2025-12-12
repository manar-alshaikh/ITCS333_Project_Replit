// ==== Elements ====
const resourceForm = document.querySelector("#resource-form");
const resourcesTableBody = document.querySelector("#resources-tbody");

const titleInput = document.querySelector("#resource-title");
const descriptionInput = document.querySelector("#resource-description");
const linkInput = document.querySelector("#resource-link");

// API BASE URL
const API_URL = "/resources/api/index.php";

// Load all resources from the API
async function loadResources() {
    try {
        const response = await fetch(API_URL);
        const data = await response.json();

        resourcesTableBody.innerHTML = "";

        data.data.forEach(resource => {
            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td>${resource.title}</td>
                <td>${resource.description}</td>
                <td>
                    <button class="delete-btn" data-id="${resource.id}">
                        Delete
                    </button>
                </td>
            `;

            resourcesTableBody.appendChild(tr);
        });

    } catch (error) {
        console.error("Error loading resources:", error);
    }
}

// Add a new resource
async function addResource(e) {
    e.preventDefault();

    const payload = {
        title: titleInput.value,
        description: descriptionInput.value,
        link: linkInput.value
    };

    const response = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    });

    const result = await response.json();

    if (result.success) {
        alert("Resource added!");
        resourceForm.reset();
        loadResources(); 
    } else {
        alert("Error: " + result.message);
    }
}

// Delete a resource
async function deleteResource(id) {
    const response = await fetch(API_URL + "?id=" + id, {
        method: "DELETE"
    });

    const result = await response.json();

    if (result.success) {
        alert("Deleted!");
        loadResources();
    } else {
        alert("Error deleting resource");
    }
}

resourceForm.addEventListener("submit", addResource);

resourcesTableBody.addEventListener("click", function (e) {
    if (e.target.classList.contains("delete-btn")) {
        const id = e.target.dataset.id;
        deleteResource(id);
    }
});

loadResources();
