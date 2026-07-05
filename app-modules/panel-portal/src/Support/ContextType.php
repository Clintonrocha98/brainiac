<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

enum ContextType: string
{
    case Project = 'project';
    case Area = 'area';
    case Collection = 'collection';
}
