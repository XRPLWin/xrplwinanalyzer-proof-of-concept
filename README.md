# XRPLWin Analyzer

WebUI is located here: https://github.com/XRPLWin/xrplwinanalyzer-ui

## Motivation

When working on xrpl.win website it has shown it is hard to get aggregated data of specific account
without querying XRPL by using markers. To show account value history and draw a graph, one needs to
fetch all account transactions, parse them and cherry pick to show some coherent data.

Doing analytics like connection between two accounts and or issuer accounts is also fairly CPU time expensive.

## About

XRPLWin Analyzer is software which analyzes and organizes XRPLedger data.
Data is fetched and stored in local/cloud database to easy access. Once data is fetched it will not
query Ledger again for the same queries, this will mitigate unnecessary requests to XRPL.

#### Account historical value

With analyzed transactions it is easy to get historical value of specific account, draw graph and pin-point
incoming transactions. This will help users to calculate gains over time for TAX purposes, viewing gains/losses etc...

#### Account connections (scam detection)

Find which accounts are connected to other accounts. This data will help to identify Token owners back to source of created account.

Token scamming is serious problem and via this feature by analyzing Issuer account, those connections will be revealed.
For example: It is possible to detect which tokens are issued same owner via direct method like account creator or indirect like
cold/hot wallets owners, and/or who holds majority (see rules) of tokens.

Point is to analyze issuers and output warnings if those tokens are in connection to "Blacklisted" account.

#### Blacklisting and account local metadata

With account (r...) there will be local metadata connected and stored in local DB. This can contain various notes and comments.

#### Custom rules

Rules will allow fine tuning when detecting connections between accounts.

## Use cases

- Dashboard for XRP accounts
- * Build UI for displaying data provided by Analyzer via REST API calls
- * Account Value by Holding Tokens and XRP over time (historical data)
- * Easy access to data for calculating TAX purposes (US citizens)
- Scam detection and prevention
- * Flagged (blacklisted) accounts and their involvement in relation to issued Tokens
- * Issued currency trend analysis and tracking
- Other...

## Analyzed data sharing between Analyzer Nodes

For each analyzed account some time is allocated for that work to be done, this can be resource expensive for
analyzer server and for XRPLedger.

When running multiple instances (nodes) of XRPLWin Analyzer, (eg. official hosted on analyzer.xrpl.win and your own),
it is possible to pull analyzed data from other nodes in an efficient manner via JSON data dumps. For this to work both
instances need to be on same version and same code HASH.

For each instance to be aware of others, there will be official registry of nodes hosted by xrpl.win.

#### Sample workflow

Analyzer 1 (analyzer.xrpl.win) analyzes rACCOUNT...1 and finishes after X minutes/hours.
Analyzer 2 (foo.example.com) needs analyzed data of rACCOUNT...1, instead of going to ledger it can lookup that account on each
of available nodes. First successful find will be on "Analyzer 1", stream download will be pulled via e.g. https://analyzer.xrpl.win/account/rACCOUNT...1/dump.json

Analyzer 2 then parses and inserts/updates local database.

## Requirements

* Web server (Nginx)
* PHP 7.4.x
* MySQL DB
* Redis
* Varnish v4 with Tags support (optional)
* php libs: BCMath 

## Installation

See: https://laravel.com/docs/8.x/installation

Copy .env.example to .env and setup variables

```
composer install --no-dev
php artisan key:generate
php artisan config:cache
php artisan route:cache
```

## Caching

Although it is possible to cache responses in various ways it is recommended to use Redis for internal caching and
Varnish for REST API responses.

## Bug reports

If you discover a bugs within XRPLWin Analyzer, please send an e-mail to XRPLWin via [info@xrplwin.com](mailto:info@xrplwin.com).

## License

XRPLWin Analyzer is open-sourced software licensed under the [ISC License](/LICENSE.md).
