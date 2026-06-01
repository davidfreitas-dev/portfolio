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
('Senior Full Stack Developer', 'Atuação no desenvolvimento de sistemas complexos utilizando PHP 8.4 e React.', '2022-01-01', NULL, 1),
('Software Engineer', 'Desenvolvimento de APIs RESTful e microserviços.', '2020-05-01', '2021-12-31', 2),
('Junior Web Developer', 'Manutenção de sites institucionais e e-commerces.', '2018-02-15', '2020-04-30', 3);

-- 🛠️ technologies
INSERT INTO technologies (id, name, slug, image, sort_order) VALUES 
(1, 'PHP', 'php', 'php.png', 1),
(2, 'Laravel', 'laravel', 'laravel.png', 2),
(3, 'Slim Framework', 'slim-framework', 'slim.png', 3),
(4, 'React', 'react', 'react.png', 4),
(5, 'TypeScript', 'typescript', 'typescript.png', 5),
(6, 'Docker', 'docker', 'docker.png', 6),
(7, 'MySQL', 'mysql', 'mysql.png', 7);

-- 📦 projects
INSERT INTO projects (id, title, slug, summary, description, link, github_link, sort_order, is_active) VALUES 
(1, 'E-commerce Platform', 'e-commerce-platform', 'Plataforma completa de vendas online.', 'Uma plataforma robusta com suporte a múltiplos gateways de pagamento e gestão de estoque em tempo real.', 'https://ecommerce.demo', 'https://github.com/user/ecommerce', 1, 1),
(2, 'Task Management API', 'task-management-api', 'API para gerenciamento de tarefas e equipes.', 'Backend escalável construído com Slim Framework e Redis para alta performance.', NULL, 'https://github.com/user/task-api', 2, 1),
(3, 'Personal Portfolio', 'personal-portfolio', 'Este projeto que você está vendo agora!', 'Um CMS customizado para gerenciar portfólios profissionais de forma eficiente.', 'https://portfolio.demo', 'https://github.com/user/portfolio', 3, 1);

-- 🔗 project_technologies
INSERT INTO project_technologies (project_id, technology_id) VALUES 
(1, 1), (1, 2), (1, 7),
(2, 3), (2, 6), (2, 7),
(3, 1), (3, 3), (3, 4), (3, 5);
