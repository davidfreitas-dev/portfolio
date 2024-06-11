import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../pages/Home.vue')
    },
    {
      path: '/experiences',
      name: 'experiences',
      component: () => import('../pages/Experiences.vue')
    },
    {
      path: '/projects',
      name: 'projects',
      component: () => import('../pages/Projects.vue')
    },
    {
      path: '/techs',
      name: 'techs',
      component: () => import('../pages/Techs.vue')
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('../pages/Settings.vue')
    }
  ]
});

export default router;
