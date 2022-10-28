#include "graphe.h"
#include <stdlib.h>

struct graphe* init_graphe(int n){
    struct graphe *G = (struct graphe*)malloc(sizeof(struct graphe));
    G->Head = (int*)malloc((n+1)*sizeof(int));
    G->Succ = (int*)malloc(sizeof(int));
    G->Potent = (double*)malloc(sizeof(double));
    return G;
}


void add_graphe(struct graphe* G, int n){
    G->Succ = (int*)realloc(G->Succ, n * sizeof(int));
    G->Potent = (double*)realloc(G->Potent, n * sizeof(double));
}


void clear_graphe(struct graphe* G){
    free(G->Head);
    free(G->Succ);
    free(G->Potent);
    free(G);
}