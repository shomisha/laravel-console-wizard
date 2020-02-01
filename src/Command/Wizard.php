<?php

namespace Shomisha\LaravelConsoleWizard\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Shomisha\LaravelConsoleWizard\Contracts\Question;
use Shomisha\LaravelConsoleWizard\Exception\InvalidQuestionException;

abstract class Wizard extends Command
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private $questions;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $asked;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $answers;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->initializeQuestions();
        $this->initializeAnswers();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    final public function handle()
    {
        do {
            $name = $this->questions->keys()->first();
            /** @var \Shomisha\LaravelConsoleWizard\Contracts\Question $question */
            $question = $this->questions->shift();

            $this->asking($question, $name);

            $answer = $question->ask($this);

            $this->answered($question, $name, $answer);
        } while ($this->questions->isNotEmpty());

        $this->completed();
    }

    private function initializeQuestions()
    {
        $this->assertQuestionsAreValid($questions = $this->getQuestions());

        $this->questions = collect($questions);

        $this->asked = collect([]);
    }

    private function initializeAnswers()
    {
        $this->answers = collect([]);
    }

    private function assertQuestionsAreValid(array $questions)
    {
        foreach ($questions as $question) {
            if (! ($question instanceof Question)) {
                throw new InvalidQuestionException($question);
            }
        }
    }

    private function asking(Question $question, string $name)
    {
        if ($this->hasAskingModifier($name)) {
            $this->{$this->guessAskingModifier($name)}($question);
        }
    }

    private function hasAskingModifier(string $name)
    {
        return method_exists($this, $this->guessAskingModifier($name));
    }

    private function guessAskingModifier(string $name)
    {
        return sprintf('asking%s', Str::studly($name));
    }

    final private function answered(Question $question, string $name, $answer)
    {
        if ($this->hasAnsweredModifier($name)) {
            $answer = $this->{$this->guessAnsweredModifier($name)}($question, $answer);
        }

        $this->addAnswer($name, $answer);

        $this->moveQuestionToAsked($name);
    }

    private function hasAnsweredModifier(string $name)
    {
        return method_exists($this, $this->guessAnsweredModifier($name));
    }

    private function guessAnsweredModifier(string $name)
    {
        return sprintf('answered%s', Str::studly($name));
    }

    private function addAnswer(string $name, $answer)
    {
        $this->answers->put($name, $answer);
    }

    private function getAnswer(string $name)
    {
        return $this->answers->get($name);
    }

    private function moveQuestionToAsked(string $name)
    {
        $question = $this->questions->pull($name);

        if ($question) {
            $this->asked->put($name, $question);
        }
    }

    abstract function getQuestions(): array;

    abstract function completed();
}
