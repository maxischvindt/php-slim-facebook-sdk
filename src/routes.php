<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
  // Sample log message
  $this->logger->info("Slim-Skeleton '/' route");
  
  // Render index view
  return $this->renderer->render($response, 'index.phtml', $args);
});

//curl -i -X GET "https://graph.facebook.com/v2.11/{id}?fields=first_name%2Clast_name&access_token={access_token}"
$app->get('/profile/facebook/{id}', function (Request $request, Response $response, array $args) {

    $my_conf = $this->get('settings')['my_conf'];

    $id = $request->getAttribute('id');
    
    $fb = new \Facebook\Facebook([
        'app_id' => $my_conf['app_id'],
        'app_secret' => $my_conf['app_secret'],
        'default_graph_version' => 'v2.11',
        'default_access_token' => $my_conf['access_token']
      ]);

    $error = false;
    try {
          $data = $fb->get("/{$id}?fields=first_name,last_name");
        } catch(FacebookExceptionsFacebookResponseException $e) {
          $error = $e->getMessage();
        } catch(FacebookExceptionsFacebookSDKException $e) {
          $error = $e->getMessage();
        } catch(FacebookResponseException $e) {
          $error = $e->getMessage();
        } catch(\Exception $e) {
          $error = $e->getMessage();
        }

        if($error) {
            $response->getBody()->write(json_encode(array('error' => $error)));
        } else {
            $graphNode = $data->getGraphNode();
            $response->getBody()->write($graphNode->asJson());
        }

        $newResponse = $response->withHeader('Content-type', 'application/json');
        
        return $newResponse;
});
