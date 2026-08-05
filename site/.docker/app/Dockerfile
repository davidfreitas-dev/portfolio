FROM nginx:alpine AS development
COPY . /usr/share/nginx/html
EXPOSE 80

FROM nginx:alpine AS production
COPY . /usr/share/nginx/html
EXPOSE 80
