SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 👥 roles
INSERT INTO roles (id, name, description) VALUES 
(1, 'admin', 'Administrador com acesso total ao sistema'),
(2, 'user', 'Usuário padrão do sistema');

-- 🔐 users
-- Senha para ambos: Admin@123 e User@123
INSERT INTO users (id, name, email, phone, password, role_id, is_active) VALUES 
(1, 'Admin Portfolio', 'admin@portfolio.com', '11999999999', '$2y$12$VJZpojI0znQi5./xsxa6F.13dPBoAdjF8r5KqDXhZThozbaSY4lF6', 1, 1),
(2, 'Usuário Teste', 'user@test.com', '11888888888', '$2y$12$DsV3RkNmgNSU4u5QnqqboO0YclN.lZo9jaZkIvB5H2YNWEgBQSOFS', 2, 1);

-- 📜 experiences
INSERT INTO experiences (title, description, start_date, end_date, sort_order) VALUES 
('Formação Técnica em Informática - ETEC', 'Início da formação técnica voltada à análise e desenvolvimento de sistemas, com foco em práticas de mercado e metodologias de desenvolvimento de projetos.', '2015-02-01', NULL, 1),
('Sistema de Agendamento em Saúde (TCC)', 'Desenvolvimento de um sistema Full Stack para agendamento de consultas. A solução permitia a triagem prévia do paciente para otimização do atendimento. Tecnologias: C#, ASP.NET, SQL Server, Bootstrap e JavaScript.', '2016-06-01', NULL, 2),
('Graduação em ADS - UNICID', 'Início da graduação superior com aprofundamento em arquitetura de software, padrões de projeto e engenharia de requisitos através da modelagem de sistemas de gestão.', '2019-08-01', NULL, 3),
('Dev. Full Stack - Kinature Cosméticos', 'Desenvolvimento completo de uma plataforma de e-commerce personalizada. Responsável pela arquitetura do banco de dados, lógica de negócio e interface responsiva. Stack: PHP, MySQL, jQuery e Bootstrap 5.', '2021-01-01', NULL, 4),
('Consolidação Técnica e Projetos', 'Finalização da graduação com foco no desenvolvimento de projetos práticos e estudo de tecnologias modernas, aplicando conceitos avançados de programação em soluções reais.', '2021-06-01', NULL, 5),
('Dev. Front-end e Mobile - HTD Sistemas', 'Liderança técnica na concepção e desenvolvimento de uma aplicação móvel híbrida para o ERP Nota Brasil, com suporte offline para sincronização de pedidos. Foco em UX e performance. Stack: Vue.js, Ionic, Oracle e JavaScript.', '2021-12-01', NULL, 6);

-- 📦 projects
INSERT INTO projects (id, title, slug, summary, description, link, github_link, sort_order, is_active, technologies) VALUES 
(1, 'TimeFreela', 'timefreela', 'Web app para programadores freelancers cronometrarem, organizarem e documentarem seus trabalhos com precisão e controle financeiro por projeto.', 'O TimeFreela foi desenvolvido para freelancers gerenciarem tempo e faturamento com profissionalismo. Conta com rastreamento de tempo em tempo real com persistência, cálculo automático de valores (hora ou preço fixo), relatórios mensais e gráficos via Chart.js, exportação em PDF/CSV, backup e restauração de dados em JSON, autenticação com Firebase e suporte nativo a Dark Mode.', 'https://time-freela.web.app/', 'https://github.com/davidfreitas-dev/timefreela', 3, 1, '["Vue.js", "TypeScript", "Tailwind CSS", "Pinia", "Firebase", "Chart.js"]'),
(2, 'Spectra', 'spectra', 'Aplicativo web de calistenia progressivo (PWA), responsivo (mobile-first), 100% local-first e sem dependência de contas ou servidores externos.', 'O Spectra foi projetado para praticantes de calistenia que desejam organizar suas rotinas semanais, executar treinos guiados com o celular no chão e acompanhar a consistência e recordes pessoais com dados 100% reais — sem cadastros, sem login, sem anúncios e com total privacidade.', 'https://davidfreitas-dev.github.io/spectra/', 'https://github.com/davidfreitas-dev/spectra', 1, 1, '["Vue.js", "TypeScript", "Tailwind CSS", "Pinia", "Vite", "PWA"]'),
(3, 'Habitus', 'habitus', 'Aplicativo multiplataforma para gerenciamento e acompanhamento de hábitos diários, focado em disciplina, consistência e evolução contínua.', 'O Habitus foi projetado para auxiliar no desenvolvimento de rotinas saudáveis e produtivas através do acompanhamento contínuo de metas diárias. Oferece controle de hábitos positivos e negativos, histórico de consistência com contadores de sequência (streaks), categorização intuitiva e visualização clara de progresso. A interface mobile-first foi construída com Vue.js, TypeScript e Ionic, integrada a uma API RESTful escalável em PHP 8.4 e Slim Framework para sincronização e persistência segura de dados.', NULL, 'https://github.com/davidfreitas-dev/habitus', 2, 1, '["PHP", "Slim Framework", "TypeScript", "Vue.js", "Ionic"]');
