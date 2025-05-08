FROM python:3.8-alpine

WORKDIR /app

COPY /consumer-email/app/requirements.txt .

RUN pip install -r requirements.txt

COPY /consumer-email/app/app.py .

COPY /consumer-email/app/templates ./templates

CMD [ "python3", "app.py"]
