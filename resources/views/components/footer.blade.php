<footer class="bg-white border-t border-gray-100 pt-12 pb-8 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <a href="/" class="text-2xl font-bold text-indigo-600">MomoOpti</a>
                <p class="mt-4 text-gray-500 max-w-sm">
                    Optimisez vos frais Mobile Money en un clic. La solution SaaS pour économiser sur chaque transaction au Bénin et en Afrique.
                </p>
            </div>
            <div>
                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Liens Utiles</h4>
                <ul class="mt-4 space-y-2">
                    <li><a href="{{ route('about') }}" class="text-gray-500 hover:text-indigo-600 transition">À Propos</a></li>
                    <li><a href="{{ route('pricing') }}" class="text-gray-500 hover:text-indigo-600 transition">Tarifs</a></li>
                    <li><a href="{{ route('contact') }}" class="text-gray-500 hover:text-indigo-600 transition">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Légal</h4>
                <ul class="mt-4 space-y-2">
                    <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition">Mentions Légales</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition">Confidentialité</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm">
                &copy; {{ date('Y') }} MomoOpti. Tous droits réservés.
            </p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <!-- Social links icons here -->
            </div>
        </div>
    </div>
</footer>
