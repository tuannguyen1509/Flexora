FROM mcr.microsoft.com/playwright:v1.64.0-noble
RUN npm install --prefix /opt/flx-browser playwright@1.64.0 --no-audit --no-fund
ENV NODE_PATH=/opt/flx-browser/node_modules
WORKDIR /workspace
