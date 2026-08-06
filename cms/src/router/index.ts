import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const routes = [
  { 
    path: '/login', 
    name: 'Login',
    component: () => import('../views/auth/Login.vue') 
  },
  { 
    path: '/forgot', 
    name: 'Forgot',
    component: () => import('../views/auth/Forgot.vue') 
  },
  { 
    path: '/validate-code', 
    name: 'ValidateCode',
    component: () => import('../views/auth/ValidateCode.vue') 
  },
  { 
    path: '/reset-password', 
    name: 'ResetPassword',
    component: () => import('../views/auth/ResetPassword.vue') 
  },
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/Home.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/experiences',
    name: 'Experiences',
    component: () => import('../views/Experiences.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/technologies',
    name: 'Technologies',
    component: () => import('../views/Technologies.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/projects',
    name: 'Projects',
    component: () => import('../views/Projects.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../views/Profile.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/design-system',
    name: 'DesignSystem',
    component: () => import('../views/DesignSystem.vue'),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'Login' });
  } 
  
  next();
});

export default router;
