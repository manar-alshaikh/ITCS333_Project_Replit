/* list.js */

const listSection = document.querySelector("#resource-list-section");

const API_URL = "/api/resources";

async function loadResources() {
    try {
        const res = await fetch(API_URL);

        const data = await res.json();

        listSection.innerHTML = "";

        data.data.forEach(resource => {
            const article = document.createElement("article");

            article.innerHTML = `
                <h2>${resource.title}</h2>
                <p>${resource.description}</p>
                <a href="/resources/details?id=${resource.id}">
                    View Resource & Discussion
                </a>
            `;

            listSection.appendChild(article);
        });
    } catch (err) {
        console.error(err);
        listSection.innerHTML = "<p>Error loading resources</p>";
    }
}

loadResources();
