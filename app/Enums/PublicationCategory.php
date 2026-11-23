<?php

namespace App\Enums;

enum PublicationCategory: string
{
    case AnnualReport = 'annual_report';
    case FinancialReport = 'financial_report';
    case ProgrammeReport = 'programme_report';
    case Research = 'research';
    case PolicyBrief = 'policy_brief';
    case Evaluation = 'evaluation';
    case Toolkit = 'toolkit';
    case Governance = 'governance';

    /**
     * Human-readable label shown on the website.
     */
    public function label(): string
    {
        return match ($this) {
            self::AnnualReport => 'Annual Reports',
            self::FinancialReport => 'Financial Reports',
            self::ProgrammeReport => 'Programme Reports',
            self::Research => 'Research',
            self::PolicyBrief => 'Policy Briefs',
            self::Evaluation => 'Programme Evaluations',
            self::Toolkit => 'Toolkits & Resources',
            self::Governance => 'Policies & Governance',
        };
    }

    /**
     * Categories listed on the Policies & Accountability page.
     *
     * @return list<self>
     */
    public static function accountability(): array
    {
        return [self::AnnualReport, self::FinancialReport, self::Governance];
    }

    /**
     * Categories listed in the Research & Insights knowledge hub.
     *
     * @return list<self>
     */
    public static function research(): array
    {
        return [self::Research, self::PolicyBrief, self::Evaluation, self::Toolkit];
    }
}
