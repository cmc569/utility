# utility

### install

composer require jasonchen/utilities dev-main

### usage

### write log usage

$util = new Util();

$util->logs(&lt;repo&gt;, &lt;message strings&gt;, &lt;extra path&gt;, &lt;root path&gt;);

---

### accunix api usage

$accunix = new AccunixLineApi(&lt;bot id&gt;);

$accunix->setAccessToken(&lt;access token&gt;);

$response = $accunix->sendMessages(&lt;user token&gt;, &lt;message body&gt;);

---

### kafka rest api usage

$kafka = new KafkaRest(&lt;kafka rest url&gt;, &lt;log path&gt;);

$kafka->setTopicName(&lt;topic name&gt;);

$result = $kafka->push(&lt;value&gt;, &lt;key&gt;);

---
