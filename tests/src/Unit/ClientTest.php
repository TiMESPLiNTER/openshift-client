<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use UniversityOfAdelaide\OpenShift\Client;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use UniversityOfAdelaide\OpenShift\Objects\Route;

class ClientTest extends TestCase {

  private const HOST = 'https://somehost.com:8443';
  private const TOKEN = 'myToken';
  private const NAMESPACE = 'namespace1';


    /**
    * Setup the guzzle client for testing.
    */
    public function testGetGuzzleClient() {
        $guzzleClient = $this->getGuzzleClientMock();
        $client = new Client($guzzleClient, self::NAMESPACE);

        // Test creating the client.
        $this->assertSame(
            $guzzleClient,
            $client->getGuzzleClient(),
            'Unable to create Guzzle client.'
        );
    }

    /**
    * Test secret creation.
    */
    public function testCreateSecret() {
        $expectedSecretName = 'my-secret-name';
        $expectedUsername = 'john.doe';
        $expectedPassword = 'secret!';

        $guzzleClient = $this->getGuzzleClientMock();
        $client = new Client($guzzleClient, self::NAMESPACE);

        $responseMock = $this->getResponseMock('{}');

        $guzzleClient->expects(self::once())->method('request')->with('POST', '/api/v1/namespaces/'.self::NAMESPACE.'/secrets', [
          'query' => [],
          'body' => '{"kind":"Secret","metadata":{"name":"my-secret-name","labels":{"app":"my-secret-name"}},"type":"Opaque","data":{"username":"'.base64_encode($expectedUsername).'","password":"'.base64_encode($expectedPassword).'"}}',
          'headers' => ['Content-Type' => 'application/json'],
        ])->willReturn($responseMock);

        $response = $client->createSecret($expectedSecretName, [
          'username' => $expectedUsername,
          'password' => $expectedPassword,
        ]);

        $this->assertNotFalse(
          $response,
          'Unable to create secret - ' . print_r($response, TRUE)
        );
  }

    /**
    * Test updating a secret.
    */
    public function testUpdateSecret() {

        $expectedSecretName = 'my-secret-name';
        $expectedUsername = 'john.doe';
        $expectedPassword = 'secret!';

        $guzzleClient = $this->getGuzzleClientMock();
        $client = new Client($guzzleClient, self::NAMESPACE);

        $responseMock = $this->getResponseMock('{}');

        $guzzleClient->expects(self::once())->method('request')->with('PUT', '/api/v1/namespaces/'.self::NAMESPACE.'/secrets/' .$expectedSecretName, [
          'query' => [],
          'body' => '{"kind":"Secret","metadata":{"name":"my-secret-name","labels":{"app":"my-secret-name"}},"type":"Opaque","data":{"username":"'.base64_encode($expectedUsername).'","password":"'.base64_encode($expectedPassword).'"}}',
          'headers' => ['Content-Type' => 'application/json'],
        ])->willReturn($responseMock);

        $response = $client->updateSecret($expectedSecretName, [
          'username' => $expectedUsername,
          'password' => $expectedPassword,
        ]);

        $this->assertNotFalse(
        $response,
        'Unable to update secret.'
        );
    }

  /**
   * Test retrieving a secret.
   */
  public function testGetSecret() {
      $expectedSecretName = 'my-secret-name';

      $guzzleClient = $this->getGuzzleClientMock();
      $client = new Client($guzzleClient, self::NAMESPACE);

      $responseMock = $this->getResponseMock('{}');

      $guzzleClient->expects(self::once())->method('request')->with('GET', '/api/v1/namespaces/'.self::NAMESPACE.'/secrets/' .$expectedSecretName, [
          'query' => [],
          'headers' => ['Content-Type' => 'application/json'],
          'body' => null,
      ])->willReturn($responseMock);

    $response = $client->getSecret($expectedSecretName);

    $this->assertNotFalse(
      $response,
      'Unable to request secret.'
    );
  }

  /**
   * Test creating an image stream.
   */
  public function testCreateImageStream() {
      $this->markTestSkipped('needs rewrite');

    $response = $this->client->createImageStream(
      $this->client->generateImageStreamConfig($this->json->clientTest->artifacts . '-stream'));

    $this->assertNotFalse(
      $response,
      'Unable to create image stream.'
    );
  }

  /**
   * Test retrieving an image stream.
   */
  public function testGetImageStream() {
      $this->markTestSkipped('needs rewrite');
    $response = $this->client->getImageStream($this->json->clientTest->artifacts . '-stream');

    $this->assertNotFalse(
      $response,
      'Unable to retrieve image stream.'
    );

    $this->assertIsArray(
      $response,
      'Returned type for image stream incorrect.'
    );

  }

  /**
   * Test creating a persistent volume claim.
   */
  public function testCreatePersistentVolumeClaim1() {
      $this->markTestSkipped('needs rewrite');

    $response = $this->client->createPersistentVolumeClaim(
      $this->json->clientTest->artifacts . '-private',
      'ReadWriteMany',
      '10Gi',
      'mydeployment'
    );

    $this->assertNotFalse(
      $response,
      'Unable to create persistent volume claim.'
    );
  }

  /**
   * Test creating a second persistent volume claim.
   */
  public function testCreatePersistentVolumeClaim2() {
      $this->markTestSkipped('needs rewrite');
    $response = $this->client->createPersistentVolumeClaim(
      $this->json->clientTest->artifacts . '-public',
      'ReadWriteMany',
      '10Gi',
      'mydeployment'
    );

    $this->assertNotFalse(
      $response,
      'Unable to create persistent volume claim.'
    );
  }

  /**
   * Test creating a build config.
   */
  public function testCreateBuildConfig() {
      $this->markTestSkipped('needs rewrite');
    $data = [
      'git' => [
        'uri' => $this->json->clientTest->source->git->uri,
        'ref' => $this->json->clientTest->source->git->ref,
      ],
      'source' => [
        'type' => $this->json->clientTest->sourceStrategy->from->kind,
        'name' => $this->json->clientTest->sourceStrategy->from->name,
      ],
    ];

    $response = $this->client->createBuildConfig(
      $this->client->generateBuildConfig(
        $this->json->clientTest->artifacts . '-build',
        $this->json->clientTest->buildSecret,
        $this->json->clientTest->artifacts . '-stream:master',
        $data
      )
    );

    $this->assertNotFalse(
      $response,
      'Unable to create build config.'
    );
  }

  /**
   * Test retrieving a build config.
   */
  public function testGetBuildConfig() {
      $this->markTestSkipped('needs rewrite');
    $response = $this->client->getBuildConfig($this->json->clientTest->artifacts . '-build');

    $this->assertNotFalse(
      $response,
      'Unable to retrieve build config.'
    );

    $this->assertIsArray($response);
  }

  /**
   * Test retrieving an image stream tag.
   */
  public function getImageStreamTag() {
      $this->markTestSkipped('needs rewrite');
    $response = $this->client->getImageStreamTag($this->json->clientTest->artifacts . '-stream:master');

    $this->assertNotFalse(
      $response,
      'Unable to retrieve image stream tag'
    );
  }

  /**
   * Test creating a deployment config.
   */
  public function testCreateDeploymentConfig() {
      $this->markTestSkipped('needs rewrite');
    $deploy_env_vars = [];
    foreach ($this->json->clientTest->envVars as $env_var) {
      $deploy_env_vars[] = [
        'name' => $env_var->name,
        'value' => $env_var->value,
      ];
    }

    $data = [
      'containerPort' => 8080,
      'memory_limit' => '128Mi',
      'env_vars' => $deploy_env_vars,
      'annotations' => [
        'test' => 'tester',
      ],
      'labels' => [
        'app' => $this->json->clientTest->artifacts
      ]
    ];

    $name = $this->json->clientTest->artifacts . '-deploy';
    $image_stream_tag = $this->json->clientTest->artifacts . '-stream:master';
    $image_name = $this->json->clientTest->artifacts . '-image';

    $response = $this->client->createDeploymentConfig(
      $this->client->generateDeploymentConfig(
        $name,
        $image_stream_tag,
        $image_name,
        FALSE,
        $this->volumes,
        $data,
        []
      )
    );

    $this->assertNotFalse(
      $response,
      'Unable to create deployment config.'
    );
  }

  /**
   * Test creation of a cron job task.
   */
  public function testCreateCronJob() {
      $this->markTestSkipped('needs rewrite');
    $deploy_env_vars = [];
    foreach ($this->json->clientTest->envVars as $env_var) {
      $deploy_env_vars[] = [
        'name' => $env_var->name,
        'value' => $env_var->value,
      ];
    }

    $data = [
      'memory_limit' => '128Mi',
      'env_vars' => $deploy_env_vars,
      'annotations' => [
        'test' => 'tester',
      ],
    ];

    $name = $this->json->clientTest->artifacts . '-cron';
    $image_name = $this->json->clientTest->artifacts . '-image';

    $args = [
      '/bin/sh',
      '-c',
      'cd /code; drush -r web cron',
    ];

    $response = $this->client->createCronJob(
      $name,
      $image_name,
      '*/30 * * * *',
      FALSE,
      $args,
      $this->volumes,
      $data
    );

    $this->assertNotFalse(
      $response,
      'Unable to create cron job config.'
    );
  }

  /**
   * Test retrieving the deployment config.
   */
  public function testGetDeploymentConfig() {
      $this->markTestSkipped('needs rewrite');
    $response = $this->client->getDeploymentConfig($this->json->clientTest->artifacts . '-deploy');

    $this->assertNotFalse(
      $response,
      'Unable to retrieve deploy config.'
    );

    $this->assertIsArray($response);
  }

  /**
   * Test creating a service.
   */
  public function testCreateService() {
      $this->markTestSkipped('needs rewrite');
    $data = [
      'dependencies' => '',
      'description' => $this->json->clientTest->artifacts . '-description',
      'protocol' => 'TCP',
      'port' => 8080,
      'targetPort' => 8080,
      'deployment' => $this->json->clientTest->artifacts . '-deploy',
    ];

    $name = $this->json->clientTest->artifacts . '-service';

    $response = $this->client->createService(
      $name,
      $this->json->clientTest->artifacts . '-deploy',
      8080,
      8080,
      $this->json->clientTest->artifacts . '-deploy'
    );

    $this->assertNotFalse(
      $response,
      'Unable to create service.'
    );
  }

  /**
   * Test creating a route for the service.
   */
  public function testCreateRoute() {
      $this->markTestSkipped('needs rewrite');
    $name = $this->json->clientTest->artifacts . '-route';
    $service = $this->json->clientTest->artifacts . '-service';
    $application_domain = $this->json->clientTest->domain;

    /** @var \UniversityOfAdelaide\OpenShift\Objects\Route $route */
    $route = Route::create()
      ->setName($name)
      ->setHost($application_domain)
      ->setPath('')
      ->setInsecureEdgeTerminationPolicy('Allow')
      ->setTermination('edge')
      ->setToKind('Service')
      ->setToName($service)
      ->setToWeight(50)
      ->setWildcardPolicy('None');

    $response = $this->client->createRoute($route);

    $this->assertNotFalse(
      $response,
      'Unable to create service.'
    );
  }

  /**
   * Test deleting the route.
   */
  public function testDeleteRoute() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deleteRoute($this->json->clientTest->artifacts . '-route');

      $this->assertNotFalse(
        $response,
        'Unable to delete route.'
      );
    }
  }

  /**
   * Test deleting the service.
   */
  public function testDeleteService() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deleteService($this->json->clientTest->artifacts . '-service');

      $this->assertNotFalse(
        $response,
        'Unable to delete route.'
      );
    }
  }

  /**
   * Test deleting the cronjob.
   */
  public function testDeleteCronJob() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deleteCronJob($this->json->clientTest->artifacts . '-cron');

      $this->assertNotFalse(
        $response,
        'Unable to delete cronjob config.'
      );
    }
  }

  /**
   * Test deleting the deployment configuration.
   */
  public function testDeleteDeploymentConfig() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deleteDeploymentConfig($this->json->clientTest->artifacts . '-deploy');

      $this->assertNotFalse(
        $response,
        'Unable to delete deploy config.'
      );
    }
  }

  /**
   * Test deleting the build configuration.
   */
  public function testDeleteBuildConfig() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deleteBuildConfig($this->json->clientTest->artifacts . '-build');

      $this->assertNotFalse(
        $response,
        'Unable to delete build config.'
      );
    }
  }

  /**
   * Test deleting the persistent volume claim.
   */
  public function testDeletePersistentVolumeClaim1() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deletePersistentVolumeClaim($this->json->clientTest->artifacts . '-private');

      $this->assertNotFalse(
        $response,
        'Unable to delete persistent volume claim.'
      );
    }
  }

  /**
   * Test deleting the persistent volume claim.
   */
  public function testDeletePersistentVolumeClaim2() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deletePersistentVolumeClaim($this->json->clientTest->artifacts . '-public');

      $this->assertNotFalse(
        $response,
        'Unable to delete persistent volume claim.'
      );
    }
  }

  /**
   * Test deleting the image stream.
   */
  public function testDeleteImageStream() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deleteImageStream($this->json->clientTest->artifacts . '-stream');

      $this->assertNotFalse(
        $response,
        'Unable to delete image stream.'
      );
    }
  }

  /**
   * Test deleteing the secret.
   */
  public function testDeleteSecret() {
      $this->markTestSkipped('needs rewrite');
    if ($this->json->clientTest->delete) {
      $response = $this->client->deleteSecret($this->json->clientTest->testSecret->name);

      $this->assertNotFalse(
        $response,
        'Unable to delete secret.'
      );
    }
  }

    /**
     * @return GuzzleClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
  private function getGuzzleClientMock(): GuzzleClientInterface {
      return $this->getMockBuilder(GuzzleClientInterface::class)->getMockForAbstractClass();
  }

    private function getResponseMock($responseBody): \Psr\Http\Message\ResponseInterface {
        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMockForAbstractClass();
        $streamMock->expects(self::once())->method('getContents')->willReturn($responseBody);

        $responseMock = $this->getMockBuilder(\Psr\Http\Message\ResponseInterface::class)->getMockForAbstractClass();
        $responseMock->expects(self::once())->method('getBody')->willReturn($streamMock);

        return $responseMock;
    }
}
