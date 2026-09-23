<?php

namespace App\Livewire\Tools;

use App\Services\Tools\NotebookReadinessQuiz;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotebookReadinessCheck extends Component
{
    /** @var array<string, bool|null> */
    public array $answers = [];

    public bool $submitted = false;

    public function mount(): void
    {
        $this->answers = array_fill_keys(array_keys($this->quiz()->questions()), null);
    }

    public function submit(): void
    {
        $unanswered = array_filter($this->answers, fn ($answer) => $answer === null);

        if ($unanswered !== []) {
            $this->addError('answers', 'Responde a todas las preguntas para ver tu resultado.');

            return;
        }

        $this->submitted = true;
    }

    public function restart(): void
    {
        $this->mount();
        $this->submitted = false;
        $this->resetValidation();
    }

    /** @return array{score: int, level: string, label: string, pending: array<int, array{question: string, tip: string}>}|null */
    #[Computed]
    public function result(): ?array
    {
        return $this->submitted ? $this->quiz()->evaluate($this->answers) : null;
    }

    public function render()
    {
        return view('livewire.tools.notebook-readiness-check', [
            'questions' => $this->quiz()->questions(),
        ]);
    }

    private function quiz(): NotebookReadinessQuiz
    {
        return app(NotebookReadinessQuiz::class);
    }
}
