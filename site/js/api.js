import { CONFIG } from './config.js';

async function fetchData(endpoint) {
    try {
        const response = await fetch(`${CONFIG.API_URL}${endpoint}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        return data; 
    } catch (error) {
        console.error(`Error fetching ${endpoint}:`, error);
        return null;
    }
}

export const api = {
    getProjects: () => fetchData('/public/projects'),
    getExperiences: () => fetchData('/public/experiences'),
    getTechnologies: () => fetchData('/public/technologies'),
    getImageUrl: (folder, image) => `${CONFIG.API_URL}/images/${folder}/${image}`
};
