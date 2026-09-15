momo-optimizer/
├── allroadmap/
│   ├── project-overview.md      ← Ce fichier
│   ├── guidelines.md
│   ├── stack-technologique.md
│   ├── features.md
│   └── database-design.md
    ── architecture.md
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Livewire/           # Composants Livewire
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Network.php
│   │   ├── WithdrawalFee.php
│   │   └── OptimizationHistory.php
│   └── Services/
│       └── FeeOptimizer.php     # L'algorithme d'optimisation
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── livewire/           # Vues Livewire
│   │   ├── components/
│   │   └── layouts/
│   └── css/
│       └── app.css             # Tailwind
├── routes/
│   ├── web.php
│   └── api.php
└── public/
    ├── icons/                   # Icônes PWA
    └── manifest.json            # Configuration PWA