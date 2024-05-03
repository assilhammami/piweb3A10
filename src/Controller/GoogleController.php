<?php
namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class GoogleController extends AbstractController
{
     
     #[Route('/connect/google', name:'connect_google_start')]
     public function connectAction(ClientRegistry $clientRegistry)
    {
        // will redirect to Facebook!
        return $clientRegistry
            ->getClient('google') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([]);
    }

    /**
 * After going to Google, you're redirected back here
 * because this is the "redirect_route" you configured
 * in config/packages/knpu_oauth2_client.yaml
 *
 * @Route("/connect/google/check", name="connect_google_check")
 * @param Request $request
 * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
 */
#[Route('/connect/google/check', name: 'connect_google_check')]
public function connectCheckAction(Request $request)
{
    if (!$this->getUser()) {
        return new JsonResponse(array('status' => false, 'message' => 'User not found'));
    } else {
        return $this->redirectToRoute('home_page');
    }
}
    
}