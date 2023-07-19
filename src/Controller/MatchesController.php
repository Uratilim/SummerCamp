<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Matches;
use App\Entity\Sponsor;
use App\Form\MatchesType;
use App\Repository\MatchesRepository;
use App\Repository\TeamRepository;
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

    #[Route('/generate-matches', name: 'app_matches_generate', methods: ['GET'])]
    public function generateMatches(TeamRepository $teamRepository, EntityManagerInterface $entityManager): Response
    {
        // Fetch all teams from the repository
        $teams = $entityManager->getRepository(Team::class)->findAll();

        // Shuffle the teams to randomize the match pairings
        shuffle($teams);

        // Create an array to store the matches
        $matches = [];

        // Generate matches
        $totalTeams = count($teams);
        for ($i = 0; $i < $totalTeams - 1; $i++) {
            for ($j = $i + 1; $j < $totalTeams; $j++) {
                $team1 = $teams[$i];
                $team2 = $teams[$j];

                // Check if the teams have already played against each other
                if ($this->haveTeamsPlayed($team1, $team2, $matches)) {
                    continue;
                }

                // Generate match scores (example logic, adjust as needed)
                $score1 = random_int(0, 5);
                $score2 = random_int(0, 5);

                // Create a new match entity
                $match = new Matches();
                $match->setTeam1($team1);
                $match->setTeam2($team2);
                $match->setScore1($score1);
                $match->setScore2($score2);

                // Update the ranking based on the match results
                $this->updateRanking($team1, $team2, $score1, $score2);

                // Save the match entity to the database
                $entityManager->persist($match);

                // Add the match to the matches array
                $matches[] = $match;
            }
        }

        // Flush the changes to the database
        $entityManager->flush();

        // Render the generated matches template
        return $this->render('matches/generated_matches.html.twig', [
            'matches' => $matches,
        ]);
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
                $this->updateRanking($ranking, $team1->getId(), 3);
                $this->updateRanking($ranking, $team2->getId(), 2);
            } elseif ($score1 < $score2) {
                $this->updateRanking($ranking, $team1->getId(), 2);
                $this->updateRanking($ranking, $team2->getId(), 3);
            } else {
                $this->updateRanking($ranking, $team1->getId(), 2);
                $this->updateRanking($ranking, $team2->getId(), 2);
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
                'points' => 0,
            ];
        }

        $ranking[$teamId]['points'] += $points;
    }





    private function haveTeamsPlayed(Team $team1, Team $team2, array $matches): bool
    {
        foreach ($matches as $match) {
            $existingTeam1 = $match->getTeam1();
            $existingTeam2 = $match->getTeam2();

            if (($existingTeam1 === $team1 && $existingTeam2 === $team2) ||
                ($existingTeam1 === $team2 && $existingTeam2 === $team1)) {
                return true;
            }
        }

        return false;
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
        if ($this->isCsrfTokenValid('delete' . $match->getId(), $request->request->get('_token'))) {
            $matchesRepository->remove($match, true);
        }

        return $this->redirectToRoute('app_matches_index', [], Response::HTTP_SEE_OTHER);
    }


    public function calculateScores(): Response
    {


        return new Response('Sco');
    }
}
