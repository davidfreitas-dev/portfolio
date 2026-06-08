import { api } from './api.js';
import { initSwiper } from './swiper.js';

export async function loadPortfolio() {
    const swiperWrapper = document.querySelector('.swiper-wrapper');
    if (!swiperWrapper) return;

    const response = await api.getProjects();
    if (!response || !response.data || !response.data.projects) {
        // If API fails, we could keep the hardcoded ones or show an error
        return;
    }

    const projects = response.data.projects;
    
    // Clear existing hardcoded content
    swiperWrapper.innerHTML = '';

    projects.forEach((project, index) => {
        // Construct image URL using API helper with fallback
        const imageUrl = api.getImageUrl('projects', project.image || 'no-image.png');
        
        // Render technologies chips if available
        const techChips = (project.technologies || [])
            .map(tech => `<span class="tech-chip">${tech.name}</span>`)
            .join('');
        
        const slide = document.createElement('div');
        slide.className = 'swiper-slide portfolio';
        
        slide.innerHTML = `
            <div class="cover" style="background-image: url(${imageUrl});"></div>
            <div class="content">
                <h3>${project.title}</h3>
                <small>${project.summary || project.description}</small>
                <div class="tech-chips">
                    ${techChips}
                </div>
                <a href="${project.links?.demo || '#'}" target="_blank">
                    Ver mais <i class="las la-long-arrow-alt-right"></i>
                </a>
            </div>
        `;
        swiperWrapper.appendChild(slide);
    });

    initSwiper();
}
