# utility

### install

composer require jasonchen/utilities dev-main

### usage

### write log usage

$util = new Util();

$util->logs('Repo', 'register message strings', 'api', **DIR**);

### accunix api usage

$accunix = new AccunixLineApi(<Bot Id>);

$accunix->setAccessToken(<Access Token>);

$response = $accunix->sendMessages(<user_token>, <Message Body>);
