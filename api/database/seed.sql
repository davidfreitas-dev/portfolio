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
('Técnico em Informática - Início', 'Iniciei o Curso Técnico em Informática na ETEC Horácio Augusto da Silveira. Curso focado em análise e desenvolvimento de sistemas. Sempre desenvolvendo projetos bem elaborados e simulando ao máximo um ambiente de trabalho real.', '2015-02-01', NULL, 1),
('Técnico em Informática - Conclusão', 'No Trabalho de Conclusão do Curso, idealizei e participei do desenvolvimento de um sistema de agendamento para instituições da área da saúde, onde o usuário podia fazer o agendamento da consulta através do site, e incluir informações referentes ao seu estado de saúde para agilizar o atendimento. Tecnologias utilizadas: C#, Asp.NET e SQL Server no back-end e HTML5, CSS3, Javascript e Bootstrap 4 no front-end.', '2016-06-01', NULL, 2),
('Técnologo em Análise e Desenvolvimento de Sistemas - Início', 'Iniciei o tecnólogo em ADS na Universidade Cidade de São Paulo - UNICID. Aprofundei meus conhecimentos em padrões de projeto e desenvolvimento. Durante o curso elaborei uma documentação de um projeto para implantação de sistema de atendimento para salões de beleza.', '2019-08-01', NULL, 3),
('Implementação de E-commerce - Kinature Cosméticos', 'Fui contratado pela empresa Kinature Cosméticos, que é um distribuidor de produtos naturais no bairro Brás em São Paulo. Atuando como Full Stack, desenvolvi uma loja virtual para comercialização dos produtos da loja através da internet, com todas as funcionalidades características de um e-commerce atual. Tecnologias utilizadas: HTML5, CSS3, Javascript, Jquery, Ajax e Bootstrap 5 no front-end e PHP 7 e MySql no back-end.', '2021-01-01', NULL, 4),
('Técnologo em Análise e Desenvolvimento de Sistemas - Conclusão', 'Ao final do curso, desenvolvi alguns projetos pessoais utilizando as tecnologias que vi no decorrer, e outras que estudei por conta própria. Os projetos desenvolvidos estão no ar e podem ser acessados através dos links no menu "Portfolio".', '2021-06-01', NULL, 5),
('Desenvolvedor Front-end e Mobile - HTD Sistemas', 'Fui contratado pela empresa HTD Sistemas para atuar no desenvolvimento do front-end de um sistema ERP chamado Nota Brasil. Sempre buscando entregar a melhor experiência para o usuário, foi identificada a necessidade de um aplicativo móvel que permitisse o lançamento de pedidos de forma off-line para depois sincronizar com o ERP. Tive o privilégio de conduzir o desenvolvimento inicial deste aplicativo analisando e escolhendo a tecnologia que mais agregaria valor ao produto de acordo com a stack já utilizada pela equipe e atuando de fato no desenvolvimento do mesmo. Tecnologias utilizadas: HTML5, CSS3, Javascript, Vue JS, Ionic, Oracle. Plataformas: Android e iOS.', '2021-12-01', NULL, 6);

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
