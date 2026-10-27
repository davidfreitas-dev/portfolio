<template>
  <div>
    <input v-model="search" placeholder="Buscar projeto..." />
    <div v-if="loading">Carregando projetos...</div>
    <ul v-else>
      <li
        v-for="p in items.filter(x => x.title.toLowerCase().includes(search.toLowerCase()) && x.is_active)"
        :key="p.id"
        @click="$emit('select', p)"
      >
        {{ p.title }} - {{ p.summary }}
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  props: ['status'],
  data() {
    return {
      items: [],
      search: '',
      loading: false
    }
  },
  watch: {
    status() {
      this.load()
    }
  },
  mounted() {
    this.load()
  },
  methods: {
    async load() {
      this.loading = true
      const r = await axios.get('/admin/projects', {
        params: { is_active: this.status }
      })
      this.items = r.data.data.projects
      this.loading = false
    }
  }
}
</script>
