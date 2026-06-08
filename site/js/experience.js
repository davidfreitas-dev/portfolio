import { api } from './api.js';

export async function loadExperiences() {
    const timeline = document.querySelector('.timeline');
    if (!timeline) return;

    const response = await api.getExperiences();
    if (!response || !response.data || !response.data.experiences) return;

    const experiences = response.data.experiences;
    
    // Sort by start_date descending
    experiences.sort((a, b) => new Date(b.start_date) - new Date(a.start_date));

    timeline.innerHTML = '';

    let currentYear = null;

    experiences.forEach((exp, index) => {
        const startDate = new Date(exp.start_date);
        const year = startDate.getFullYear();

        if (year !== currentYear) {
            currentYear = year;
            const period = document.createElement('li');
            period.className = 'timeline-item period wow zoomIn';
            period.setAttribute('data-wow-duration', '500ms');
            period.setAttribute('data-wow-delay', '300ms');
            period.innerHTML = `
                <div class="timeline-info"></div>
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <h2 class="timeline-title">${year}</h2>
                </div>
            `;
            timeline.appendChild(period);
        }

        const item = document.createElement('li');
        const isOdd = exp.sort_order % 2 !== 0;
        const orderClass = isOdd ? 'timeline-item-odd' : 'timeline-item-even';
        item.className = `timeline-item ${orderClass} wow zoomIn`;
        item.setAttribute('data-wow-duration', '500ms');
        item.setAttribute('data-wow-delay', `${400 + (index * 10) % 500}ms`);
        
        const monthNames = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
            "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
        ];
        const month = monthNames[startDate.getMonth()];

        item.innerHTML = `
            <div class="timeline-info">
                <span>${month} ${year}</span>
            </div>
            <div class="timeline-marker"></div>
            <div class="timeline-content">
                <h3 class="timeline-title">${exp.title}</h3>
                <p>${exp.description}</p>
            </div>
        `;
        timeline.appendChild(item);
    });
}
