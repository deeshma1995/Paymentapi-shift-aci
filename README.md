# Payment Processing API
This is a payment system built with Symfony that supports Shift4 and ACI providers. It lets you process payments via an API or console commands .

#### Requirements:
- Symfony 6.4
- PHP 8.2


$ composer install
```
##### Services

- http://localhost/ - Access the application

### API Endpoint

You can access it in Postman and input JSON body params:
$ /app/example/{aci|shift4}

Example for Shift4:

{
    "amount": 100,
    "currency": "USD",
    "card_number": "4909069612259316",
    "exp_month": "10",
    "exp_year": "2026",
    "cvv": "123",
    "cardholder_name": "Adam Joe"
}

Example for ACI"

{
    "amount": 499,
    "currency": "EUR",
    "card_number": "4200000000000000",
    "exp_month": "11",
    "exp_year": "2027",
    "cvv": "123",
    "cardholder_name": "Joe Doe"
}


### CLI Command

Run with following commands:

$ bin/console app:example {aci|shift4}

Try with following inputs:

For Shift4:

php bin/console app:example shift4 100 USD 4909069612259316 10 2026 123 "Adam Doe"

For ACI:


 php bin/console app:example aci 499 EUR 4200000000000000 11 2027 123 "John Doe"


