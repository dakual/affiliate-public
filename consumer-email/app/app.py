#!/usr/bin/env python
import pika, sys, os, logging, json
import smtplib, ssl

from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from jinja2 import Environment, FileSystemLoader

MQ_HOST = os.environ['MQ_HOST']
MQ_PORT = os.environ['MQ_PORT']
MQ_USER = os.environ['MQ_USER']
MQ_PASS = os.environ['MQ_PASS']

SMTP_HOST = os.environ['SMTP_HOST']
SMTP_PORT = os.environ['SMTP_PORT']
SMTP_USER = os.environ['SMTP_USER']
SMTP_PASS = os.environ['SMTP_PASS']

logging.basicConfig(
  level    = logging.INFO,
  format   = "%(asctime)s [%(levelname)s] %(message)s",
  handlers = [
    logging.StreamHandler(sys.stdout)
  ]
)

def main():
  credentials = pika.PlainCredentials(MQ_USER, MQ_PASS)
  connection  = pika.BlockingConnection(
    pika.ConnectionParameters(
      host=MQ_HOST, 
      port=MQ_PORT, 
      virtual_host='/', 
      credentials=credentials
    )
  )
  channel = connection.channel()
  channel.queue_declare(queue='email', durable=True)

  def callback(ch, method, properties, body):
    body = json.loads(body)
    logging.info(f'[{body["template"]["name"]}] Received for {body["recipient"]["email"]}')

    try:
      templateName = f'{body["template"]["name"]}.html'
      environment  = Environment(loader=FileSystemLoader("templates/"))
      template     = environment.get_template(templateName)
      content      = template.render(body) 
    except Exception as e:
      logging.info("Template not found!")
      return
    
    message = MIMEMultipart("alternative")
    message.attach(MIMEText(content, "html"))
    message["Subject"] = body["template"]["subject"]
    message["From"]    = "hello@e-russkiy.com"
    message["To"]      = body["recipient"]["email"]

    try:
      with smtplib.SMTP(SMTP_HOST, int(SMTP_PORT)) as server:
        # server.set_debuglevel(1)
        server.ehlo()
        server.starttls(context=ssl.create_default_context())
        server.ehlo()
        server.login(SMTP_USER, SMTP_PASS)
        server.sendmail(
            message["From"], message["To"], message.as_string()
        )
    except Exception as e:
      logging.info(e)
    

  channel.basic_consume(queue='email', on_message_callback=callback, auto_ack=True)

  logging.info('[*] Waiting for messages. To exit press CTRL+C')
  channel.start_consuming()

if __name__ == '__main__':
  try:
    main()
  except KeyboardInterrupt:
    logging.error('Interrupted')
    try:
      sys.exit(0)
    except SystemExit:
      os._exit(0)