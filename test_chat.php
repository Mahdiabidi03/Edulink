<?php
require 'vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$contextService = $container->get(App\Service\ContextService::class);
$users = $container->get('doctrine')->getRepository(App\Entity\User::class)->findAll();
foreach ($users as $user) {
    try {
        $contextService->getUserContext($user);
    } catch (\Throwable $e) {
        echo 'Error for User ' . $user->getId() . ': ' . $e->getMessage() . "\n";
    }
}
echo "Finished testing all users.\n";
