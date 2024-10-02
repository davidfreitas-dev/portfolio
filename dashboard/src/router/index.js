import { createRouter, createWebHistory } from 'vue-router';
import { useSessionStore } from '../stores/session';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/signin',
      name: 'Signin',
      component: () => import('../pages/auth/Signin.vue')
    },
    {
      path: '/forgot',
      name: 'Forgot',
      component: () => import('../pages/auth/Forgot.vue')
    },
    {
      path: '/forgot/reset',
      name: 'Reset',
      component: () => import('../pages/auth/Reset.vue')
    },
    {
      path: '/',
      redirect: '/home'
    },
    {
      path: '/home',
      name: 'Home',
      component: () => import('../pages/Home.vue'),
      meta: { 
        requiresAuth: true 
      }
    },
    {
      path: '/experiences',
      name: 'Experiences',
      component: () => import('../pages/Experiences.vue'),
      meta: { 
        requiresAuth: true 
      }
    },
    {
      path: '/projects',
      name: 'Projects',
      component: () => import('../pages/Projects.vue'),
      meta: { 
        requiresAuth: true 
      }
    },
    {
      path: '/techs',
      name: 'Techs',
      component: () => import('../pages/Techs.vue'),
      meta: { 
        requiresAuth: true 
      }
    },
    {
      path: '/settings',
      name: 'Settings',
      component: () => import('../pages/Settings.vue'),
      meta: { 
        requiresAuth: true 
      }
    }
  ]
});

router.beforeEach((to, from, next) => {
  const storeSession = useSessionStore();

  const invalidSession = !storeSession.session || !storeSession.session.token;

  if (to.meta.requiresAuth && invalidSession) {
    next('/signin');
  } else {
    next();
  }
});

export default router;
