FROM mattrayner/lamp:latest-1604
RUN apt-get update && apt-get install -y npm nodejs
RUN ln -s /usr/bin/nodejs /usr/bin/node
RUN echo '{ "allow_root": true }' > /root/.bowerrc
EXPOSE 80 3306
CMD ["/run.sh"]
