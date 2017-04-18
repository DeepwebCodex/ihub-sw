FROM alpine

VOLUME /ihub

COPY . /ihub/

ENTRYPOINT ["ls"]