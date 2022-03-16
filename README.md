# utility

### install

composer require jasonchen/utilities dev-main

### usage

### write log usage

$util = new Util();

$util->logs('Repo', 'register message strings', 'api', **DIR**);

### accunix api usage

$accunix = new AccunixLineApi(<bot id>);

$accunix->setAccessToken(<access token>);

$response = $accunix->sendMessages(<user token>, <message body>);

### kafka rest api usage

$kafka = new KafkaRest(<kafka rest url>, <log path>);

$kafka->setTopicName(<topic name>);

$result = $kafka->push(<value>, <key>);
