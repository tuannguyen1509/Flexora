#!/bin/sh
set -eu
flx_host=${FLX_URL#*://}
export HTTP_HOST=${flx_host%%/*}
if ! wp core is-installed; then
  wp core install --url="$FLX_URL" --title="Flexora Development" --admin_user="$FLX_ADMIN_USER" --admin_password="$FLX_ADMIN_PASSWORD" --admin_email="$FLX_ADMIN_EMAIL" --skip-email
fi
