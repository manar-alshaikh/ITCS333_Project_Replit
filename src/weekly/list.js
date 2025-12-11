




const listSection = document.getElementById('week-list-section');




function createWeekArticle(week) {
  const article = document.createElement('article');

  const heading = document.createElement('h2');
  heading.textContent = week.title;

  const startDate = document.createElement('p');
  startDate.textContent = `Starts on: ${week.startDate || "-"}`;

  const description = document.createElement('p');
  description.textContent = week.description;

  
  const detailsLink = document.createElement('a');
  detailsLink.href = `./details.php?id=${week.id}`;
  detailsLink.textContent = 'View Details & Discussion';

  article.appendChild(heading);
  article.appendChild(startDate);
  article.appendChild(description);
  article.appendChild(detailsLink);

  return article;
}





async function loadWeeks() {
  try {
    const response = await fetch('./api/index.php');
    const result = await response.json();

    if (!result.success) {
      listSection.innerHTML = '<p>Error loading weeks.</p>';
      return;
    }

    const weeks = result.data.map(w => ({
      id: Number(w.id),
      title: w.title,
      startDate: w.start_date,     
      description: w.description
    }));

    listSection.innerHTML = '';

    weeks.forEach(week => {
      const article = createWeekArticle(week);
      listSection.appendChild(article);
    });

  } catch (error) {
    console.error('Error loading weeks:', error);
    listSection.innerHTML =
      '<p>Error loading weekly breakdown. Please try again later.</p>';
  }
}





loadWeeks();
