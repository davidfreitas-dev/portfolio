---
name: vue3-refactor
description: Refactors Vue 3 views (pages, routed components), forms, and UI components (.vue files) using Composition API with <script setup lang="ts">, standard block ordering and clean code rules for the Portfolio CMS. Use when the user asks to refactor, clean up, reorganize, review or split a Vue view, form or component, or to extract composables from one.
---

# Vue 3 Refactor Skill (Views, Forms e Componentes)

## Objetivo
Refatorar views, formulários ou componentes Vue 3 da aplicação **Portfolio CMS** (`cms/`) para que sigam os padrões de arquitetura (Composition API + `<script setup lang="ts">`, Design System nativo, Pinia, TailwindCSS 4 e tipagem estrita), **sem alterar o comportamento funcional ou visual esperado**.

---

## 📋 Instruções de Refatoração

1. **Leitura Completa do Arquivo Alvo**:
   Inspecione todo o arquivo antes de qualquer edição. Identifique responsabilidades: rotas, chamadas à Store/API, validação de regras de negócio, debounce de buscas, controle de modais e marcação visual.

2. **Classifique o Alvo**:
   - **View (`src/views/`)**: Ponto de entrada roteado. Orquestra Stores (`useProjectsStore`), paginação, busca e modais (`Modal`, `Dialog`).
   - **Form (`src/forms/`)**: Formulário desacoplado de cadastro/edição. Recebe `initialData`, valida com `@vuelidate/core` e emite `@submit` / `@cancel`.
   - **Component (`src/components/`)**: Elemento visual desacoplado (*Dumb Component*). Apenas recebe props e emite eventos (`Button`, `Badge`, `Table`).

3. **Diagnóstico Pré-Refatoração**:
   Utilize `references/clean-code-rules.md` e `references/views.md`. Liste brevemente os problemas identificados antes de editar:
   - Lógica pesada dentro do `<template>` (deve ir para `computed`).
   - Falta de tipagem ou uso de `any` (substituir por interfaces de `src/types/`).
   - Acesso direto ao Axios em componentes/formulários (mover para Store/Service).
   - Componentes grandes acumulando muitas seções (extrair para subcomponentes ou formulários).

4. **Reordenação do Arquivo**:
   Siga a convenção de `references/script-setup-order.md`:
   - Blocos do SFC: `<script setup lang="ts">` → `<template>` → `<style scoped>`.
   - Ordem do Script: Imports → Props/Emits/Model → Stores/Composables → State (`ref`) → Computed → Watchers → Funções/Handlers → Lifecycle hooks → `defineExpose`.

5. **Passo a Passo da Refatoração**:
   1. Mova expressões complexas do template para `computed`.
   2. Renomeie variáveis e funções para nomes auto-explicativos com intenção clara.
   3. Isole regras de validação usando `@vuelidate/core` se for um formulário.
   4. Extraia lógicas de estado extensas para composables em `src/composables/useXxx.ts`.
   5. Substitua tags HTML cruas (`<button>`, `<input>`) pelos componentes do Design System (`Button`, `Input`, `Select`, etc.).

6. **Validação de Tipos nas Fronteiras**:
   Garanta tipagem estrita para todas as props, emits, payloads de submit e respostas da API sem nenhum uso de `any`.

7. **Limpeza**:
   Remova código morto, imports desnecessários e `console.log`.

---

## 🚫 Restrições

- **NÃO** altere comportamentos esperados, rotas, props públicas ou eventos emitidos sem consentimento explícito.
- **NÃO** misture refatoração com novas features no mesmo ciclo de alteração.
- **NÃO** desestruture props de forma que quebre a reatividade do Vue.
- **NÃO** utilize Options API sob nenhuma hipótese.
- **NÃO** permita que componentes base de UI em `src/components/` acessem Stores ou Rotas diretamente.
