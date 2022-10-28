#include <stdbool.h>
#include <stdio.h>
#include <stdlib.h>
#include "graphe.h"

static void lecture_fichier(int argc, char *argv[],int* n,int* Q, int **q, double ***dist, int* dep){
    /*
        Données:
            argc : entier, nombre d'argument
            argv : matrice de taille argv*x de char, tableau d'argument
        Résultat:
            n: entier, nombre de client
            Q: entier, quantité maximal possible dans un camion
            q: tableau d'entier de taille n, quantité demander par un client
            dist: matrice de double de taille n+1*n+1, table d'adjacence
            dep: entier, client de départ
        Locales:
            k,i,j: entier, indice de boucle
    */
    scanf("%d", n); //Lecture du nombre de client 
    scanf("%d", Q); //Lecture de la quantité maximal possible dans un camion
    //Lecture de la quantité demander par un client
    *q =(int *) malloc(*n * sizeof(int));
    
    for (int k = 0; k < *n; k++)
    {
        scanf("%d", *q + k);
    }
    // Initialisation du tableau des distance à l’aide du fichier
    *dist = (double**)malloc((*n+1)*sizeof(double*));
    for(int i=0; i<*n+1; i++){
        (*dist)[i] = (double*)malloc((*n+1)*sizeof(double));
    }
    for (int i = 0; i < *n + 1; i++)
    {
        for (int j = 0; j < *n + 1; j++)
        {
            scanf("%lf", &((*dist)[i][j]));
            
        }
    }
    //Initialisation du client de départ avec gestion d'erreur
    if (argc==1){
        fprintf(stderr,"Il faut un argument pour ce programme. Initialisation du départ à 1.\n");
        *dep=1;
    }else if(argc==2){
        *dep = atoi(argv[1]);
    }else{
        fprintf(stderr,"Il faut un seul argument pour ce programme. Initialisation du départ à la valeur du premier argument.\n");
        *dep = atoi(argv[1]);
    }
    if (*dep<1){
        fprintf(stderr,"Un client a un numéro strictement positif. Initialisation du départ à 1.\n");
        *dep = 1;
    }else if(*dep>*n){
        fprintf(stderr,"Un client a un numéro inférieur ou égal à %d. Initialisation du départ à %d.\n",*n,*n);
        *dep = *n;
    }
}

static void tourGeant(int n, double **dist, int *T, int dep){
    /*
        Données:
            n : entier, nombre de clients
            dist : matrices de taille n+1*n+1 d’entiers, distances
            dep : entier, client à livrer en premier
        Résultat:
            T : vecteur de taille n d’entiers, tour géant
        Locales:
            mark : vecteur de taille n de booléens, répertorie les clients déjà dans le tour
            next : entier, distance minimale en partant du dernier client ajouté au tour
            k : entier, indice de parcours du vecteur mark
            i : entier, indice de parcours du vecteur T
            col : entier, indice de parcours des colonnes d’une ligne de dist
    */
    // On initialise
    // Aucun sommet n’est marqué par le tour donc on initialise mark en conséquence
    bool mark[n];
    for (int k = 0; k < n; k++)
    {
        mark[k] = false;
    }
    // On ajoute le premier élément qui est le client de départ choisit et on le marque
    mark[dep - 1] = true;
    T[0] = dep;
    double next;
    // On recherche le client le plus proche du dernier client ajouté qui soit non marqué
    for (int i = 0; i < n - 1; i++)
    {
        // On initialise next avec une valeur particulière
        next = -1;
        //
        for (int col = 1; col < n + 1; col++)
        {
            // Si le sommet n’est pas marqué et que next est à son état initialisé ou est supérieur à la distance actuelle
            if (!mark[col - 1] && (next == -1 || dist[T[i]][col] < next))
            {
                // La distance minimale devient la distance actuelle
                next = dist[T[i]][col];
                T[i + 1] = col;
            }
        }
        mark[T[i + 1] - 1] = true;
    }
}

static void split (int n, int *m, double **dist, int T[n], int Q, int q[n], struct graphe *G){
	/*
        Donnée :
            dist: matrice d’entier de taille n+1*n+1, distances <=> poids arcs
            T: vecteur d’entier de taille n, tour géant
            q: vecteur d’entier de taille n, unité nécessaire par client
            n: entier, nombre de client
            Q: entier, capacité véhicule
        Résultat:
            G: pointe vers une structure de donnée graphe, contenant les champs suivants:
                Head: vecteur d’entier de taille n+1 
                    (le dernier sommet n’as pas de successeur on lui assigne donc la taille de Succ)
                Succ: vecteur d’entier de taille m, table des successeurs
                Potent: vecteur d’entier de taille m, table des poids (distances)
            m: pointe vers un entier, nombre d’arcs du graphe auxiliaire
        Locales:
            i, j: entier, indices de boucle
            load: entier, quantité demandé pour tous les clients de la boucle
            cost: entier, coût de la boucle courante
    */
    // On initialise
	int i, j, load;
    double cost;
    G->Head[0] = 0;
    *m=0;
	for(i = 0 ; i < n ; i++){
		j = i; 
        load = 0; 
        G->Head[i+1] = G->Head[i];
		do{
			load = load + q[T[j]-1];
			if ( i == j ){
				// On calcul la distance parcourue pour desservir un client
                cost = dist[0][T[i]] + dist[0][T[i]];
            }else{
                // On calcul la distance parcourue pour desservir les client de la boucle
                cost = cost - dist[T[j-1]][0] + dist[T[j-1]][T[j]] + dist[T[j]][0];
            }
            if ( load <= Q ) {
                *m += 1;
                add_graphe(G, *m);
                G->Succ[G->Head[i+1]] = j+1; // initialisation de Succ
                G->Potent[G->Head[i+1]] = cost; // initialisation du poid de l’arc
                G->Head[i+1] += 1; // incrémentation de la valeur Head suivante
            }
            j += 1;
        }while( (j < n) && (load < Q) ); // Tant qu'on n'a des clients et qu'on a pas dépasser la charge maximale d'un camion
	}
}

static void pcc(int n, int m, struct graphe *G, int pere[n], double pi[n]){
	/*
        Donnée :
            n: entier, nombre de client
            m: entier, nombre d’arcs du graphe auxiliaire
            G: pointe vers une structure de donnée graphe, contenant les champs suivants:
                Head: vecteur d’entier de taille n+1 
                    (le dernier sommet n’as pas de successeur on lui assigne donc la taille de Succ)
                Succ: vecteur d’entier de taille m, table des successeurs
                Potent: vecteur d’entier de taille m, table des poids (distances)
        Résultat:
            pere: vecteur d’entier de taille n, table des pères de chacun
            pi: vecteur d’entier de taille n, table du poids minimal de chacun
        Locales:
            k, i, j: entiers, indices de boucle
	*/
    // On initialise Père à 0 et Pi à -1
    for(int k = 0 ; k < n+1 ; k++){ 
        pere[k] = 0; 
        pi[k] = -1; 
    }
	// On ajoute la racine au pcc
	pere[0] = 0; 
    pi[0] = 0;
	// Pour tout successeur x de r
	for (int i=G->Head[0]; i<G->Head[1]; i++){
	    pi[G->Succ[i]]=G->Potent[i];
	    pere[G->Succ[i]]=0;
    }
	for(int i =  1; i < n ; i++){
		// Pour tout successeur y de x tel que pi[x] + w(xy) < pi[y] w(xy) est la distance entre x et y
        for (int j = G->Head[i] ; j < G->Head[i+1] ; j++){
            if((pi[G->Succ[j]]==-1)||(pi[i] + G->Potent[j] < pi[G->Succ[j]])){
            // On choisis le plus court chemin pour atteindre ce successeur
                pi[G->Succ[j]] = pi[i] + G->Potent[j];
                pere[G->Succ[j]] = i;
            }
        }
	}
}

static void graphe_final(int *pere, double *pi, int n, int* T){
    /*
        Données :
            n: entier, nombre de client
            pere: vecteur d’entier de taille n, table des pères de chacun
            pi: vecteur d’entier de taille n, table du poids minimal de chacun
            T : vecteur de taille n d’entiers, tour géant
        Locales:
            sommet_courant: entier, indice du sommet courant
            nb_etape: entier, nombre d'étapes effectuées
            chemin: vecteur d’entier de la taille du pcc, reconstitution du pcc
            nb_clients_boucle: entier, nombre de clients dans la boucle courante
            cout_boucle: double, cout de la boucle courante
            s, u: entiers, indices de boucle
	*/
    int sommet_courant = n;
    int nb_etape = 0;
    int *chemin = (int*)malloc(sizeof(int));
    int nb_clients_boucle;
    double cout_boucle;
    while(sommet_courant != 0){ // Récupération du pcc à l’envers à l’aide de pere
        chemin[nb_etape] = sommet_courant;
        sommet_courant = pere[sommet_courant];
        nb_etape++;
        chemin = (int*)realloc(chemin, (nb_etape+1) * sizeof(int));
    }
    chemin[nb_etape] = sommet_courant;
    nb_etape++;
    printf("Au total : %d camions et un coût de %lf.\n", nb_etape-1, pi[n]);
    for(int s = nb_etape - 1 ; s > 0 ; s -= 1){ // Parcours du pcc à l’endroit (boucle)
        nb_clients_boucle = chemin[s-1] - chemin[s];// Récupération du nombre de clients livré par la boucle
        cout_boucle = pi[chemin[s-1]]-pi[chemin[s]];// Récupération du coût de la boucle
        int clients_boucle[nb_clients_boucle];
        // Début de l’affichage d’une boucle pour un camion
        printf("D->");
        for(int u = 0 ; u < nb_clients_boucle ; u++){
            // Récupération des clients de la boucle à partir des indices
            clients_boucle[u] = T[chemin[s] + u];
            // Affichage clients de la boucle
            printf("%d->", clients_boucle[u]);
        }
        // Fin de l’affichage d’une boucle et affichage du coût de la boucle
        printf("D : %lf\n", cout_boucle);
    }
    free(chemin);
}

int main(int argc, char *argv[])
{
    // LECTURE DU FICHIER
    int n;
    int Q;
    int *q;
    double **dist;
    int dep;
    lecture_fichier(argc, argv, &n, &Q, &q, &dist, &dep);
    // TOUR GÉANT
    int *T = (int*)malloc(n * sizeof(int));
    tourGeant(n, dist, T, dep);
    /*for (int w = 0; w < n; w++)
    {
        printf(" %d ", T[w]);
    }
    printf("\n\n");*/ 
    // GRAPHE AUXILIAIRE
    struct graphe *G = init_graphe(n);
    int m;
    split(n, &m, dist, T, Q, q, G);
    /*for (int w = 0; w < n+1; w++)
    {
        printf(" %d ", G->Head[w]);
    }
    printf("\n");
    for (int w = 0; w < m; w++)
    {
        printf(" %d ", G->Succ[w]);
    }
    printf("\n");
    for (int w = 0; w < m; w++)
    {
        printf(" %lf ", G->Potent[w]);
    }
    printf("\n"); 
    
    for(int i=0; i<n+1; i++){
        free(dist[i]);
    }*/
    free(dist);
    free(q);

    //PCC
    int *pere = (int*)malloc((n+1)*sizeof(int));
    double *pi = (double*)malloc((n+1)*sizeof(double));
    pcc(n, m, G, pere, pi);
    /*for (int w = 0; w < n+1; w++)
    {
        printf(" %d ", pere[w]);
    }
    printf("\n");
    for (int w = 0; w < n+1; w++)
    {
        printf(" %lf ", pi[w]);
    }
    printf("\n");*/
    clear_graphe(G);

    //GRAPHE FINAL
    graphe_final(pere, pi, n, T);
    free(pere);
    free(T);
    free(pi);
}
