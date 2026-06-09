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

async function postData(endpoint, data) {
    try {
        const response = await fetch(`${CONFIG.API_URL}${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw { 
                status: response.status, 
                message: result.message || 'Erro na requisição',
                errors: result.errors || null
            };
        }
        
        return result;
    } catch (error) {
        console.error(`Error posting to ${endpoint}:`, error);
        throw error;
    }
}

export const api = {
    getProjects: () => fetchData('/public/projects'),
    getExperiences: () => fetchData('/public/experiences'),
    getTechnologies: () => fetchData('/public/technologies'),
    sendContactRequest: (data) => postData('/public/contact', data),
    getImageUrl: (folder, image) => `${CONFIG.API_URL}/images/${folder}/${image}`
};
