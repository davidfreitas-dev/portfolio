import { ref, watch } from 'vue';
import { defineStore } from 'pinia';

export const useSessionStore = defineStore('session', () => {
  const session = ref(null);

  const setSession = async (data) => {
    session.value = data;
  };

  const clearSession = async () => {
    sessionStorage.clear();
    session.value = null;
  };

  if (sessionStorage.getItem('session')) {
    session.value = JSON.parse(sessionStorage.getItem('session'));
  }
  
  watch(
    session,
    newSession => {
      sessionStorage.setItem('session', JSON.stringify(newSession));
    },
    { deep: true }
  );

  return { 
    session, 
    setSession,
    clearSession 
  };
});