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

$app->get('/profile/facebook/{id}', function (Request $request, Response $response, array $args) {

    $id = $request->getAttribute('id');
    $my_conf = $this->get('settings')['my_conf'];
    $error = false;

    if(!isset($my_conf['access_token']) || empty($my_conf['access_token'])) {
      $response->getBody()->write(json_encode(array('error' => array("message" => "access token required"))));
      $newResponse = $response->withHeader('Content-type', 'application/json');
      return $newResponse;
    }


    // Por CURL
    if(!$my_conf['app_fb']) {

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v2.11/{$id}?fields=first_name%2Clast_name&access_token={$my_conf['access_token']}");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $result = curl_exec($ch);
      if (curl_errno($ch)) {
          $error = curl_error($ch);
      }
      curl_close ($ch);

      if($error) {
        $response->getBody()->write(json_encode(array('error' => array("message" => $error))));
      } else {
        $response->getBody()->write($result);
      }

    } else {
      
      // Por SDK
      $fb = new \Facebook\Facebook([
          'app_id' => $my_conf['app_id'],
          'app_secret' => $my_conf['app_secret'],
          'default_graph_version' => 'v2.11',
          'default_access_token' => $my_conf['access_token']
        ]);

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
          $response->getBody()->write(json_encode(array('error' => array("message" => $error))));
      } else {
          $graphNode = $data->getGraphNode();
          $response->getBody()->write($graphNode->asJson());
      }
    }

    $newResponse = $response->withHeader('Content-type', 'application/json');
    
    return $newResponse;
});

$app->get('/config/check', function (Request $request, Response $response, array $args) {

  $my_conf = $this->get('settings')['my_conf'];
  $errors = array();

  if(!isset($my_conf['access_token']) || empty($my_conf['access_token'])) {
    $errors[] = array("message" => "access token required");
  }

  if($my_conf['app_fb']) {
    
    if(!isset($my_conf['app_id']) || empty($my_conf['app_id'])) {
      $errors[] = array("message" => "app_id required");
    }
    
    if(!isset($my_conf['app_secret']) || empty($my_conf['app_secret'])) {
      $errors[] = array("message" => "app_secret required");
    }
  }

  if($errors) {
    $response->getBody()->write(json_encode($errors));
  } else {
    $response->getBody()->write(json_encode(array('result' => 'ok')));
  }

  $newResponse = $response->withHeader('Content-type', 'application/json');
  
  return $newResponse;
});
