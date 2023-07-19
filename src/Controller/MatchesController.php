<?php

namespace App\Controller;
use App\Entity\Team;
use App\Entity\Matches;
use App\Entity\Sponsor;
use App\Form\MatchesType;
use App\Repository\MatchesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/matches')]
class MatchesController extends AbstractController
{
    #[Route('/', name: 'app_matches_index', methods: ['GET'])]
    public function index(MatchesRepository $matchesRepository): Response
    {
        return $this->render('matches/index.html.twig', [
            'matches' => $matchesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_matches_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MatchesRepository $matchesRepository): Response
    {
        $match = new Matches();
        $form = $this->createForm(MatchesType::class, $match);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $matchesRepository->save($match, true);

            return $this->redirectToRoute('app_matches_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('matches/new.html.twig', [
            'match' => $match,
            'form' => $form,
        ]);
    }
    #[Route('/populate', name: 'app_matches_populate', methods: ['GET'])]
    public function populate(EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $faker = Factory::create();
        for ($i=0;$i<10;$i++) {
            $matches = new matches();
            $matches->setTeam1();
            $matches->setTeam2();
            $matches->setScore1();
            $matches->setScore2();
            $matches->setDatetime();
            $matches->setReferee();

            $entityManager->persist($matches);

            $entityManager->flush();


        }
        return new Response('Saved match with id' . $matches->getId());
    }
    public function calculateRanking(MatchesRepository $matchesRepository): Response
    {
        $matches = $matchesRepository->findAll();

        $ranking = [];

        foreach ($matches as $match) {
            $team1 = $match->getTeam1();
            $team2 = $match->getTeam2();
            $score1 = $match->getScore1();
            $score2 = $match->getScore2();
            
            if ($score1 > $score2) {
                $this->updateRanking($ranking, $team1, 3);
                $this->updateRanking($ranking, $team2, 2);
            } elseif ($score1 < $score2) {
                $this->updateRanking($ranking, $team1, 2);
                $this->updateRanking($ranking, $team2, 3);
            } else {
                $this->updateRanking($ranking, $team1, 2);
                $this->updateRanking($ranking, $team2, 2);
            }
        }

        usort($ranking, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        return $this->render('matches/ranking.html.twig', [
            'ranking' => $ranking,
        ]);
    }

    private function updateRanking(array &$ranking, Team $team, int $points): void
    {
        $teamId = $team->getId();

        if (!isset($ranking[$teamId])) {
            $ranking[$teamId] = [
                'team' => $team,
                'points' => $points,
            ];
        } else {
            $ranking[$teamId]['points'] += $points;
        }
    }
    #[Route('/{id}', name: 'app_matches_show', methods: ['GET'])]
    public function show(Matches $match): Response
    {
        return $this->render('matches/show.html.twig', [
            'match' => $match,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_matches_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Matches $match, MatchesRepository $matchesRepository): Response
    {
        $form = $this->createForm(MatchesType::class, $match);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $matchesRepository->save($match, true);

            return $this->redirectToRoute('app_matches_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('matches/edit.html.twig', [
            'match' => $match,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_matches_delete', methods: ['POST'])]
    public function delete(Request $request, Matches $match, MatchesRepository $matchesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$match->getId(), $request->request->get('_token'))) {
            $matchesRepository->remove($match, true);
        }

        return $this->redirectToRoute('app_matches_index', [], Response::HTTP_SEE_OTHER);
    }



    public function calculateScores(): Response
    {


        return new Response('Sco');
    }
}
