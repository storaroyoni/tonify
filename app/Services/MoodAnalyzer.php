<?php

namespace App\Services;

class MoodAnalyzer
{
    private $lastFmService;
    private $moodCategories = [
        'energetic' => ['energetic', 'upbeat', 'dance', 'party', 'power', 'hype', 'fast', 'epic', 'dance-pop', 'electronic'],
        'melancholic' => ['sad', 'melancholic', 'emotional', 'depression', 'heartbreak', 'dark', 'yearning', 'ambient'],
        'calm' => ['calm', 'peaceful', 'relaxing', 'ambient', 'chill', 'mellow', 'soft', 'chillwave', 'dream pop'],
        'happy' => ['happy', 'feel good', 'summer', 'fun', 'joy', 'uplifting', 'sweet', 'romantic', 'beautiful'],
        'angry' => ['angry', 'aggressive', 'metal', 'rage', 'intense', 'hard', 'punk', 'hardcore']
    ];

    public function __construct(LastFmService $lastFmService)
    {
        $this->lastFmService = $lastFmService;
    }

    public function analyzeTracks($tracks)
    {
        if (empty($tracks)) {
            return $this->getDefaultAnalysis();
        }

        $moodScores = array_fill_keys(array_keys($this->moodCategories), 0);
        $moodTracks = array_fill_keys(array_keys($this->moodCategories), []);
        $totalTagsAnalyzed = 0;

        foreach ($tracks as $track) {
            $artistName = $track['artist']['#text'] ?? $track['artist'] ?? null;
            $trackName = $track['name'] ?? null;

            if (!$artistName || !$trackName) {
                \Log::warning('Missing track info:', ['track' => $track]);
                continue;
            }

            $tags = $this->lastFmService->getTrackTags($artistName, $trackName);
            
            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    if (!isset($tag['name'])) {
                        continue;
                    }
                    
                    $tagName = strtolower($tag['name']);
                    foreach ($this->moodCategories as $mood => $keywords) {
                        if ($this->tagMatchesMood($tagName, $keywords)) {
                            $moodScores[$mood]++;
                            $totalTagsAnalyzed++;
                            
                            if (!in_array($trackName, array_column($moodTracks[$mood], 'name'))) {
                                $moodTracks[$mood][] = [
                                    'name' => $trackName,
                                    'artist' => $artistName,
                                    'tag' => $tag['name']
                                ];
                            }
                        }
                    }
                }
            }
        }

        if ($totalTagsAnalyzed === 0) {
            return $this->getDefaultAnalysis();
        }

        foreach ($moodScores as &$score) {
            $score = ($score / $totalTagsAnalyzed) * 100;
        }

        $energyLevel = ($moodScores['energetic'] + $moodScores['angry']) / 2;
        $happinessLevel = ($moodScores['happy'] - $moodScores['melancholic'] + 100) / 2;

        return [
            'primary_mood' => ucfirst($this->getPrimaryMood($moodScores)),
            'mood_scores' => $moodScores,
            'mood_tracks' => $this->getMoodTracks($moodScores, $moodTracks),
            'energy_level' => round($energyLevel),
            'happiness_level' => round($happinessLevel)
        ];
    }

    private function getPrimaryMood($moodScores)
    {
        return array_search(max($moodScores), $moodScores);
    }

    private function tagMatchesMood($tag, $keywords)
    {
        foreach ($keywords as $keyword) {
            if (str_contains($tag, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function getDefaultAnalysis()
    {
        return [
            'primary_mood' => 'Unknown',
            'mood_scores' => array_fill_keys(array_keys($this->moodCategories), 0),
            'mood_tracks' => array_fill_keys(array_keys($this->moodCategories), []),
            'energy_level' => 0,
            'happiness_level' => 50
        ];
    }

    private function getMoodTracks($moodScores, $tracksWithMoods) 
    {
        $moodTracks = [];
        $usedTracks = []; // Keep track of songs we've already assigned
        
        // Sort moods by score descending so stronger moods get priority
        arsort($moodScores);
        
        foreach ($moodScores as $mood => $score) {
            $tracks = collect($tracksWithMoods[$mood] ?? [])
                ->filter(function($track) use ($usedTracks) {
                    // Create a unique identifier for the track
                    $trackId = $track['artist'] . ' - ' . $track['name'];
                    // Only include if we haven't used this track yet
                    return !in_array($trackId, $usedTracks);
                })
                ->unique('name')
                ->take(3)
                ->values()
                ->all();
            
            // Add these tracks to our used tracks list
            foreach ($tracks as $track) {
                $usedTracks[] = $track['artist'] . ' - ' . $track['name'];
            }
            
            $moodTracks[$mood] = $tracks;
        }
        
        return $moodTracks;
    }
} 