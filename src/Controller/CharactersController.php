<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;

class CharactersController extends AbstractController
{
    #[Route('/characters', name: 'app_characters')]
    public function index(HttpClientInterface $httpClient): Response
    {
      $locationList = $this->fetchLocations($httpClient);
      $dimensionList = $this->fetchDimensions($httpClient);
      $episodeList = $this->fetchEpisodes($httpClient);

      return $this->render('index.html.twig', [
          'locationList' => array_keys($locationList),
          'dimensionList' => array_unique($dimensionList),
          'episodeList' => array_keys($episodeList)
      ]);
    }

  // SEARCH BY LOCATION
    private function fetchLocations(HttpClientInterface $httpClient): array
    {
      $LOCATIONS = [];
      $nextPageUrl = 'https://rickandmortyapi.com/api/location';

      do {
          $response = $httpClient->request('GET', $nextPageUrl);
          $content = $response->toArray();

          foreach ($content['results'] as $location) {
              $locationName = $location['name'];
              $LOCATIONS[$locationName] = $location['id'];
          }

          $nextPageUrl = $content['info']['next'];
      } while ($nextPageUrl !== null);

      return $LOCATIONS;
    }

    #[Route('/character-information-by-location', name: 'character_information_by_location')]
    public function fetchCharacterInformationByLocation(HttpClientInterface $httpClient, Request $request): Response
    {
      $selectedLocation = $request->query->get('location');

      $locations = $this->fetchLocations($httpClient);
      $selectedLocationId = $locations[$selectedLocation];

      $response = $httpClient->request(
        'GET',
        "https://rickandmortyapi.com/api/location/" . $selectedLocationId
      );

      $content = $response->toArray();

      $charactersData = [];
      foreach ($content['residents'] as $characterUrl) {
          $characterResponse = $httpClient->request('GET', $characterUrl);
          $characterData = $characterResponse->toArray();
          $charactersData[] = $characterData;
      }

      return $this->render('character_information.html.twig', [
        'title' => 'Location - ' . $content['name'],
        'charactersData' => $charactersData,
      ]);
    }

  // SEARCH BY DIMENSION
    private function fetchDimensions(HttpClientInterface $httpClient): array
    {
      $DIMENSIONS = [];
      $nextPageUrl = 'https://rickandmortyapi.com/api/location';

      do {
          $response = $httpClient->request('GET', $nextPageUrl);
          $content = $response->toArray();

          foreach ($content['results'] as $location) {
              $dimensionName = $location['dimension'];
              $DIMENSIONS[$location['id']] = $dimensionName;
          }

          $nextPageUrl = $content['info']['next'];
      } while ($nextPageUrl !== null);

      return $DIMENSIONS;
    }

    #[Route('/character-information-by-dimension', name: 'character_information_by_dimension')]
    public function fetchCharacterInformationByDimension(HttpClientInterface $httpClient, Request $request): Response
    {
      $selectedDimension = $request->query->get('dimension');

      $dimensions = $this->fetchDimensions($httpClient);
      $locationIds = array_keys($dimensions, $selectedDimension);
      $charactersData = [];


      foreach ($locationIds as $locationId) {

        $response = $httpClient->request(
          'GET',
          "https://rickandmortyapi.com/api/location/" . $locationId
        );

        $content = $response->toArray();

        foreach ($content['residents'] as $characterUrl) {
            $characterResponse = $httpClient->request('GET', $characterUrl);
            $characterData = $characterResponse->toArray();
            $charactersData[] = $characterData;
        }
      }

      return $this->render('character_information.html.twig', [
        'title' => 'Demension - ' . $selectedDimension,
        'charactersData' => $charactersData,
      ]);
    }

  // SEARCH BY EPISODE
    private function fetchEpisodes(HttpClientInterface $httpClient): array
    {
      $EPISODES = [];
      $nextPageUrl = 'https://rickandmortyapi.com/api/episode';

      do {
          $response = $httpClient->request('GET', $nextPageUrl);
          $content = $response->toArray();

          foreach ($content['results'] as $episode) {
              $episodeNumber = $episode['episode'];
              $EPISODES[$episodeNumber] = $episode['id'];
          }

          $nextPageUrl = $content['info']['next'];
      } while ($nextPageUrl !== null);

      return $EPISODES;
    }


    #[Route('/character-information-by-episode', name: 'character_information_by_episode')]
    public function fetchCharacterInformationByEpisode(HttpClientInterface $httpClient, Request $request): Response
    {
      $selectedEpisode = $request->query->get('episode');

      $episodes = $this->fetchEpisodes($httpClient);
      $selectedEpisodeId = $episodes[$selectedEpisode];

      $response = $httpClient->request(
        'GET',
        "https://rickandmortyapi.com/api/episode/" . $selectedEpisodeId
      );

      $content = $response->toArray();

      $charactersData = [];
      foreach ($content['characters'] as $characterUrl) {
          $characterResponse = $httpClient->request('GET', $characterUrl);
          $characterData = $characterResponse->toArray();
          $charactersData[] = $characterData;
      }

      $episodeNo = $content['episode'];
      $episodeName = $content['name'];

      return $this->render('character_information.html.twig', [
          'title' => 'Episode ' . $episodeNo . " - " . $episodeName,
          'charactersData' => $charactersData,
      ]);
    }

}
