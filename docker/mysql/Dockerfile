FROM mariadb:11.4

COPY docker/mysql/entrypoint.sh /usr/local/bin/amu-mysql-entrypoint.sh
RUN chmod +x /usr/local/bin/amu-mysql-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/amu-mysql-entrypoint.sh"]
CMD ["mysqld"]
