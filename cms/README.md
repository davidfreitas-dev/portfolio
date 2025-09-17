# Time Freela Documentação Oficial

## 📚 Índice

1. [Visão Geral do Projeto](#1-visão-geral-do-projeto)
2. [Requisitos Funcionais](#2-requisitos-funcionais)
3. [Requisitos Não Funcionais](#3-requisitos-não-funcionais)
4. [Estrutura de Navegação (Mapa de Páginas)](#4-estrutura-de-navegação-mapa-de-páginas)
5. [Arquitetura Frontend (Vue 3 + Pinia)](#5-arquitetura-frontend-vue-3--pinia)
6. [Autenticação (Firebase Auth)](#6-autenticação-firebase-auth)
7. [Integração com Firestore](#7-integração-com-firestore)
8. [Geração de Relatórios](#8-geraçã-de-relatórios)
9. [Gestão Financeira](#9-gestão-financeira)
10. [Design e UX](#10-design-e-ux)
11. [Testes](#11-testes)
12. [Deploy](#12-deploy)
13. [Glossário](#13-glossário)
14. [Extras (Planejados/Futuros)](#14-extras-planejadosfuturos)

## 1. Visão Geral do Projeto

### Propósito
TimeFreela é um web app criado para **programadores freelancers** que desejam **cronometrar, organizar e documentar seus trabalhos com precisão e profissionalismo**, oferecendo também controle financeiro por projeto.

### Público-alvo
- Desenvolvedores autônomos
- Profissionais de tecnologia que trabalham com múltiplos clientes
- Agências com times freelancers

### Funcionalidades-chave
- Cronômetro e registro manual de tempo
- Organização por projeto e tags
- Painel financeiro com valor/hora e valor fixo
- UI responsiva e intuitiva

### Benefícios
- Controle profissional de tempo e faturamento
- Agilidade na criação de relatórios
- Redução de erros manuais
- Pronto para expansão (pacotes mensais)

## 2. Requisitos Funcionais

- Autenticação com Firebase (email/senha)
- Criação e gerenciamento de projetos com:
  - Título, descrição
  - Tipo de faturamento: valor por hora ou valor fixo
  - Tags/categorias
- Cronômetro com início, pausa e fim para registro automático de sessões
- Registro manual de sessões de trabalho
- Sessões organizadas por projeto e por período
- Marcação de sessões como faturadas
- Dashboard com:
  - Total de horas trabalhadas
  - Faturamento (considerando valor/hora e valor fixo dos projetos)
  - Gráfico de progressão
- Painel financeiro com filtros por período, projeto e status de faturamento, exibindo totais agregados
- Suporte a expansões futuras, como:
  - Geração de relatórios em PDF/CSV
  - Pacotes de horas
  - Modelos de cobrança personalizados

## 3. Requisitos Não Funcionais

- Responsividade mobile
- Armazenamento em nuvem (Firestore)
- Carregamento abaixo de 2s
- Regras de segurança no Firestore
- Backup automático via Firebase
- UI moderna com TailwindCSS
- Acessibilidade com `aria`, contraste e navegação por teclado

## 4. Estrutura de Navegação (Mapa de Páginas)

- `/login` – Login
- `/register` – Cadastro
- `/dashboard` – Visão geral
- `/projects` – Lista de projetos
  - `/projects/:id` – Detalhes do projeto
  - `/projects/create` – Criação do projeto
- `/sessions` – Lista de sessões
  - `/sessions/:id` – Detalhes da sessão
  - `/sessions/create` – Criação da sessão
- `/timer` – Cronômetro
- `/reports` – Relatórios
- `/settings` – Configurações

## 5. Arquitetura Frontend (Vue 3 + Pinia)

### Estrutura de Pastas
```
src/
├── assets/
├── components/
├── composables/
├── filters/
├── plugins/
├── router/
├── services/
├── stores/
└── views/
```

### Stores
- `authStore`, `userStore`, `projectStore`, `sessionStore`, `reportStore`, `timerStore`

### Composables
- `useTimer`, `useBilling`, `useExport`, `useLoading`, `useToast`, `useBeforeUnloadGuard`

### Roteamento
- SPA com vue-router, lazy loading, middleware de proteção

## 6. Autenticação (Firebase Auth)

- Login com email e senha
- Persistência com `onAuthStateChanged`
- Middleware de proteção
- Armazenado na `authStore`

## 7. Integração com Firestore

### Estrutura
```
users/{userId}
  - name
  - email
  - image
  - createdAt
  - updatedAt 

projects/{projectId}
  - userId
  - title
  - description
  - tags
  - billingType
  - billingAmount
  - active
  - createdAt
  - updatedAt 

sessions/{sessionId}
  - projectId
  - userId
  - duration
  - isManual
  - isBilled
  - date
  - startTime
  - endTime
  - createdAt
  - updatedAt 
```

### Segurança
- Regras baseadas em `userId`
- Escritas com batch para múltiplas sessões

## 8. Geração de Relatórios

- Filtros por projeto, cliente, status
- Totais de horas, valores e faturamento
- CSV com agrupamentos
- PDF com jsPDF

## 9. Gestão Financeira

- Valor/hora ou preço fixo por projeto
- Sessões mostram valor estimado
- Marcação: a faturar, faturada
- Painel financeiro com totais
- Planejado: pacotes mensais

## 10. Design e UX

- TailwindCSS, responsivo
- Cores: azul, cinza escuro, branco
- Dark mode nativo
- UI acessível

## 11. Testes

- Unitários com Vitest
- Integração nas stores
- Componentes com Vue Test Utils
- Cobertura mínima recomendada: 80%

## 12. Deploy

- Firebase Hosting
- Domínio customizado
- CI/CD com GitHub Actions

## 13. Glossário

| Termo            | Definição |
|------------------|----------|
| Sessão           | Tempo cronometrado/manual de trabalho |
| Faturada         | Sessão paga ou enviada para cliente |
| Valor/hora       | Preço por hora definido no projeto |
| Projeto fixo     | Projeto com valor fechado |
| Pacote mensal    | Cobrança recorrente mensal |
| Tag              | Classificação de projeto ou sessão |

## 14. Extras (Planejados/Futuros)

- Mini-timer flutuante
- Relatórios em PDF/CSV
- Notificações por e-mail sobre faturamento
- Suporte a contratos, pacotes e valor por funcionalidade